use crate::app::{App, AppState, ActiveInput};
use ratatui::{
    layout::{Constraint, Direction, Layout, Rect},
    style::{Color, Modifier, Style},
    text::{Line, Span},
    widgets::{Block, Borders, List, ListItem, ListState, Paragraph, Wrap},
    Frame,
};

pub fn render(f: &mut Frame, app: &App) {
    let chunks = Layout::default()
        .direction(Direction::Vertical)
        .constraints([
            Constraint::Length(3), // Header
            Constraint::Min(0),    // Content
            Constraint::Length(3), // Status bar
        ])
        .split(f.area());

    render_header(f, chunks[0], app);
    render_content(f, chunks[1], app);
    render_status_bar(f, chunks[2], app);
}

fn render_header(f: &mut Frame, area: Rect, app: &App) {
    let title = if let Some(user) = &app.user {
        format!("Forum TUI - Logged in as {}", &user.public_key[..12])
    } else {
        "Forum TUI - Not logged in".to_string()
    };

    let header = Paragraph::new(title)
        .block(Block::default().borders(Borders::ALL).title("Forum TUI"))
        .style(Style::default().fg(Color::White).bg(Color::Blue));

    f.render_widget(header, area);
}

fn render_content(f: &mut Frame, area: Rect, app: &App) {
    match &app.state {
        AppState::Login => render_login(f, area, app),
        AppState::Boards => render_boards(f, area, app),
        AppState::Threads(board_code) => render_threads(f, area, app, board_code),
        AppState::ThreadDetail(board_code, thread_id) => render_thread_detail(f, area, app, board_code, *thread_id),
        AppState::CreateThread(board_code) => render_create_thread(f, area, app, board_code),
        AppState::CreateReply(board_code, thread_id) => render_create_reply(f, area, app, board_code, *thread_id),
        AppState::Help => render_help(f, area),
    }
}

fn render_login(f: &mut Frame, area: Rect, app: &App) {
    let chunks = Layout::default()
        .direction(Direction::Vertical)
        .constraints([
            Constraint::Length(3), // Private key input
            Constraint::Min(0),    // Instructions
        ])
        .split(area);

    // Private key input
    let private_key_style = if app.active_input == ActiveInput::PrivateKey {
        Style::default().fg(Color::Yellow)
    } else {
        Style::default()
    };

    let private_key_input = Paragraph::new(app.private_key_input.content.as_str())
        .block(
            Block::default()
                .borders(Borders::ALL)
                .title("Private Key (hex)")
                .border_style(private_key_style)
        );

    f.render_widget(private_key_input, chunks[0]);

    // Instructions
    let instructions = Paragraph::new(
        "Instructions:\n\
        1. Enter your private key in hex format (64 characters)\n\
        2. Press Enter to login\n\
        \n\
        The client will automatically derive your public key, get a challenge,\n\
        sign it with your private key, and authenticate with the server.\n\
        \n\
        Controls: Enter to login, q to quit, h for help"
    )
    .block(Block::default().borders(Borders::ALL).title("Login Instructions"))
    .wrap(Wrap { trim: true });

    f.render_widget(instructions, chunks[1]);
}

fn render_boards(f: &mut Frame, area: Rect, app: &App) {
    let items: Vec<ListItem> = app.boards
        .iter()
        .map(|board| {
            ListItem::new(Line::from(vec![
                Span::raw(format!("/{}/", board.code)),
                Span::raw(" - "),
                Span::raw(&board.name),
                Span::raw(" ("),
                Span::raw(format!("{} threads", board.threads_count)),
                Span::raw(")"),
            ]))
        })
        .collect();

    let list = List::new(items)
        .block(
            Block::default()
                .borders(Borders::ALL)
                .title("Boards - Use ↑↓ to navigate, Enter to select, h for help")
        )
        .highlight_style(Style::default().fg(Color::Yellow).add_modifier(Modifier::BOLD));

    let mut list_state = ListState::default();
    list_state.select(Some(app.selected_index));

    f.render_stateful_widget(list, area, &mut list_state);
}

fn render_threads(f: &mut Frame, area: Rect, app: &App, board_code: &str) {
    let items: Vec<ListItem> = app.threads
        .iter()
        .map(|thread| {
            ListItem::new(Line::from(vec![
                Span::raw(&thread.title),
                Span::raw(" by "),
                Span::raw(&thread.author_name),
                Span::raw(" ("),
                Span::raw(format!("{} posts", thread.posts_count)),
                Span::raw(")"),
            ]))
        })
        .collect();

    let title = format!("Threads in /{board_code}/ - Use ↑↓ to navigate, Enter to view, n to create, b for boards, h for help");
    let list = List::new(items)
        .block(Block::default().borders(Borders::ALL).title(title))
        .highlight_style(Style::default().fg(Color::Yellow).add_modifier(Modifier::BOLD));

    let mut list_state = ListState::default();
    list_state.select(Some(app.selected_index));

    f.render_stateful_widget(list, area, &mut list_state);
}

fn render_thread_detail(f: &mut Frame, area: Rect, app: &App, _board_code: &str, _thread_id: u32) {
    if let Some(detail) = &app.thread_detail {
        let chunks = Layout::default()
            .direction(Direction::Vertical)
            .constraints([
                Constraint::Length(5), // Thread header
                Constraint::Min(0),    // Posts
            ])
            .split(area);

        // Thread header
        let thread_content = format!(
            "Title: {}\nAuthor: {}\nCreated: {}\n\n{}",
            detail.thread.title,
            detail.thread.author_name,
            detail.thread.created_at.format("%Y-%m-%d %H:%M:%S"),
            detail.thread.content
        );

        let thread_header = Paragraph::new(thread_content)
            .block(Block::default().borders(Borders::ALL).title("Thread"))
            .wrap(Wrap { trim: true });

        f.render_widget(thread_header, chunks[0]);

        // Posts - now using flattened posts with proper tree visualization and parent info
        let flattened_posts = app.get_flattened_posts();
        
        // Show all posts and let the Paragraph widget handle scrolling naturally
        let posts_text: Vec<Line> = flattened_posts
            .iter()
            .enumerate()
            .flat_map(|(i, (post, tree_prefix, parent_id))| {
                let is_selected = i == app.selected_index;
                
                let author_style = if is_selected {
                    Style::default().fg(Color::Yellow).add_modifier(Modifier::BOLD)
                } else {
                    Style::default().fg(Color::Cyan)
                };
                
                let content_style = if is_selected {
                    Style::default().fg(Color::Yellow)
                } else {
                    Style::default()
                };
                
                vec![
                    Line::from(vec![
                        Span::raw(tree_prefix.clone()),
                        Span::styled(format!("By: {}", post.author_name), author_style),
                        Span::raw(" - "),
                        Span::styled(
                            post.created_at.format("%Y-%m-%d %H:%M:%S").to_string(),
                            Style::default().fg(Color::Gray)
                        ),
                        Span::styled(format!(" [ID: {}]", post.id), Style::default().fg(Color::DarkGray)),
                        // Show which post this is replying to
                        if let Some(parent_id) = parent_id {
                            Span::styled(format!(" (replying to #{}))", parent_id), Style::default().fg(Color::Blue))
                        } else {
                            Span::raw("")
                        },
                    ]),
                    Line::from(vec![
                        Span::raw(if tree_prefix.is_empty() { 
                            String::new() 
                        } else { 
                            // For content lines, replace tree characters with spaces for proper alignment
                            tree_prefix.chars().map(|c| match c {
                                '├' | '└' | '─' => ' ',
                                '│' => '│',
                                _ => c,
                            }).collect::<String>()
                        }),
                        Span::styled(post.content.clone(), content_style),
                    ]),
                    Line::from(Span::raw("")),
                ]
            })
            .collect();

        let title = format!("Posts ({}/{}) - ↑↓ to select, r to reply to thread, Shift+R to reply to selected post, b for boards, t for threads, h for help", 
                           app.selected_index + 1, flattened_posts.len());

        // Calculate scroll offset based on selected post position
        let available_height = chunks[1].height.saturating_sub(2) as usize; // subtract borders
        let lines_per_post = 3; // author line + content line + empty line
        let selected_line_approx = app.selected_index * lines_per_post;
        let scroll_offset = if selected_line_approx >= available_height {
            selected_line_approx.saturating_sub(available_height / 2)
        } else {
            0
        };

        let posts_widget = Paragraph::new(posts_text)
            .block(
                Block::default()
                    .borders(Borders::ALL)
                    .title(title)
            )
            .wrap(Wrap { trim: true })
            .scroll((scroll_offset as u16, 0));

        f.render_widget(posts_widget, chunks[1]);
    } else {
        let loading = Paragraph::new("Loading thread...")
            .block(Block::default().borders(Borders::ALL).title("Thread Detail"));

        f.render_widget(loading, area);
    }
}

fn render_create_thread(f: &mut Frame, area: Rect, app: &App, board_code: &str) {
    let chunks = Layout::default()
        .direction(Direction::Vertical)
        .constraints([
            Constraint::Length(3), // Title input
            Constraint::Min(0),    // Content input
            Constraint::Length(3), // Instructions
        ])
        .split(area);

    // Title input
    let title_style = if app.active_input == ActiveInput::ThreadTitle {
        Style::default().fg(Color::Yellow)
    } else {
        Style::default()
    };

    let title_input = Paragraph::new(app.thread_title_input.content.as_str())
        .block(
            Block::default()
                .borders(Borders::ALL)
                .title("Thread Title")
                .border_style(title_style)
        );

    f.render_widget(title_input, chunks[0]);

    // Content input
    let content_style = if app.active_input == ActiveInput::ThreadContent {
        Style::default().fg(Color::Yellow)
    } else {
        Style::default()
    };

    let content_input = Paragraph::new(app.thread_content_input.content.as_str())
        .block(
            Block::default()
                .borders(Borders::ALL)
                .title("Thread Content")
                .border_style(content_style)
        )
        .wrap(Wrap { trim: true });

    f.render_widget(content_input, chunks[1]);

    // Instructions
    let instructions = Paragraph::new(format!(
        "Creating thread in /{board_code}/\n\
        Tab to switch fields, Ctrl+S to submit, Esc to cancel"
    ))
    .block(Block::default().borders(Borders::ALL).title("Instructions"));

    f.render_widget(instructions, chunks[2]);
}

fn render_create_reply(f: &mut Frame, area: Rect, app: &App, board_code: &str, thread_id: u32) {
    let chunks = Layout::default()
        .direction(Direction::Vertical)
        .constraints([
            Constraint::Min(0),    // Content input
            Constraint::Length(3), // Instructions
        ])
        .split(area);

    // Content input
    let content_style = if app.active_input == ActiveInput::ReplyContent {
        Style::default().fg(Color::Yellow)
    } else {
        Style::default()
    };

    let content_input = Paragraph::new(app.reply_content_input.content.as_str())
        .block(
            Block::default()
                .borders(Borders::ALL)
                .title("Reply Content")
                .border_style(content_style)
        )
        .wrap(Wrap { trim: true });

    f.render_widget(content_input, chunks[0]);

    // Instructions
    let reply_target = if let Some(post_id) = app.selected_post_id {
        format!("Replying to post #{}", post_id)
    } else {
        "Replying to thread".to_string()
    };
    
    let instructions = Paragraph::new(format!(
        "Creating reply in /{board_code}/ thread #{thread_id}\n\
        {}\n\
        Ctrl+S to submit, Esc to cancel",
        reply_target
    ))
    .block(Block::default().borders(Borders::ALL).title("Instructions"));

    f.render_widget(instructions, chunks[1]);
}

fn render_help(f: &mut Frame, area: Rect) {
    let help_text = "Forum TUI Help\n\n\
        Global Controls:\n\
        • h - Show this help\n\
        • q - Quit application\n\
        • Esc - Go back/cancel current action\n\n\
        Login:\n\
        • Enter - Login with private key\n\n\
        Navigation:\n\
        • ↑↓ - Navigate lists\n\
        • Enter - Select item\n\
        • b - Go to boards\n\n\
        Boards:\n\
        • Enter - View board threads\n\n\
        Threads:\n\
        • Enter - View thread detail\n\
        • n - Create new thread\n\
        • t - Refresh threads\n\n\
        Thread Detail:\n\
        • ↑↓ - Navigate posts\n\
        • r - Reply to thread\n\
        • Shift+R - Reply to selected post\n\
        • t - Back to threads\n\n\
        Creating Content:\n\
        • Tab - Switch input fields\n\
        • Ctrl+S - Submit\n\
        • Esc - Cancel";

    let help_widget = Paragraph::new(help_text)
        .block(Block::default().borders(Borders::ALL).title("Help - Press any key to close"))
        .wrap(Wrap { trim: true });

    f.render_widget(help_widget, area);
}

fn render_status_bar(f: &mut Frame, area: Rect, app: &App) {
    let status_text = if let Some(error) = &app.error_message {
        // Truncate long error messages and clean HTML content
        let clean_error = if error.contains("<!DOCTYPE html>") || error.contains("<html") {
            if error.contains("HTTP Error") {
                // Extract just the HTTP error part
                error.lines().next().unwrap_or("HTTP Error").to_string()
            } else {
                "Server returned HTML error page".to_string()
            }
        } else {
            // Limit error message length for display
            if error.len() > 100 {
                format!("{}...", &error[..97])
            } else {
                error.clone()
            }
        };
        format!("ERROR: {}", clean_error)
    } else {
        app.status_message.clone()
    };

    let status_style = if app.error_message.is_some() {
        Style::default().fg(Color::Red)
    } else {
        Style::default().fg(Color::Green)
    };

    let status_bar = Paragraph::new(status_text)
        .block(Block::default().borders(Borders::ALL))
        .style(status_style)
        .wrap(Wrap { trim: true });

    f.render_widget(status_bar, area);
}
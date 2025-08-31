use crate::app::{App, AppState, ActiveInput};
use anyhow::Result;
use crossterm::event::{KeyCode, KeyEvent, KeyModifiers};
use log::debug;

pub async fn handle_key_event(app: &mut App, key_event: KeyEvent) -> Result<()> {
    // If user is actively typing, handle input first and skip global keybinds
    if app.active_input != ActiveInput::None {
        debug!("Handling key {:?} in input mode (active_input: {:?}), modifiers: {:?}", key_event.code, app.active_input, key_event.modifiers);
        match key_event.code {
            KeyCode::Esc => {
                handle_escape(app).await?;
            }
            _ => {
                match &app.state {
                    AppState::CreateThread(board_code) => {
                        let board_code = board_code.clone();
                        handle_create_thread_keys(app, key_event, &board_code).await?;
                    }
                    AppState::CreateReply(board_code, thread_id) => {
                        let board_code = board_code.clone();
                        let thread_id = *thread_id;
                        handle_create_reply_keys(app, key_event, &board_code, thread_id).await?;
                    }
                    AppState::Login => handle_login_keys(app, key_event).await?,
                    _ => {
                        handle_input_keys(app, key_event);
                    }
                }
            }
        }
        return Ok(());
    }

    // Global keybinds (only when not typing)
    match key_event.code {
        KeyCode::Char('q') => {
            app.quit();
        }
        KeyCode::Char('h') => {
            app.state = AppState::Help;
        }
        KeyCode::Esc => {
            handle_escape(app).await?;
        }
        _ => {
            match &app.state {
                AppState::Login => handle_login_keys(app, key_event).await?,
                AppState::Boards => handle_boards_keys(app, key_event).await?,
                AppState::Threads(board_code) => {
                    let board_code = board_code.clone();
                    handle_threads_keys(app, key_event, &board_code).await?;
                }
                AppState::ThreadDetail(board_code, thread_id) => {
                    let board_code = board_code.clone();
                    let thread_id = *thread_id;
                    handle_thread_detail_keys(app, key_event, &board_code, thread_id).await?;
                }
                AppState::CreateThread(board_code) => {
                    let board_code = board_code.clone();
                    handle_create_thread_keys(app, key_event, &board_code).await?;
                }
                AppState::CreateReply(board_code, thread_id) => {
                    let board_code = board_code.clone();
                    let thread_id = *thread_id;
                    handle_create_reply_keys(app, key_event, &board_code, thread_id).await?;
                }
                AppState::Help => {
                    // Any key closes help
                    app.state = if app.api_client.is_authenticated() {
                        AppState::Boards
                    } else {
                        AppState::Login
                    };
                }
            }
        }
    }

    Ok(())
}

async fn handle_escape(app: &mut App) -> Result<()> {
    match &app.state {
        AppState::Login => {
            // Can't escape from login if not authenticated
            if !app.api_client.is_authenticated() {
                return Ok(());
            }
            app.state = AppState::Boards;
        }
        AppState::Boards => {
            // Can't escape from boards - this is the main menu when authenticated
        }
        AppState::Threads(_) => {
            app.state = AppState::Boards;
        }
        AppState::ThreadDetail(board_code, _) => {
            let board_code = board_code.clone();
            app.state = AppState::Threads(board_code.clone());
            app.load_threads(&board_code).await?;
        }
        AppState::CreateThread(board_code) => {
            let board_code = board_code.clone();
            app.thread_title_input.clear();
            app.thread_content_input.clear();
            app.active_input = ActiveInput::None;
            app.state = AppState::Threads(board_code);
        }
        AppState::CreateReply(board_code, thread_id) => {
            let board_code = board_code.clone();
            let thread_id = *thread_id;
            app.reply_content_input.clear();
            app.selected_post_id = None;
            app.active_input = ActiveInput::None;
            app.state = AppState::ThreadDetail(board_code, thread_id);
        }
        AppState::Help => {
            app.state = if app.api_client.is_authenticated() {
                AppState::Boards
            } else {
                AppState::Login
            };
        }
    }

    Ok(())
}

async fn handle_login_keys(app: &mut App, key_event: KeyEvent) -> Result<()> {
    match key_event.code {
        KeyCode::Enter => {
            app.login().await?;
        }
        _ => {
            handle_input_keys(app, key_event);
        }
    }

    Ok(())
}

async fn handle_boards_keys(app: &mut App, key_event: KeyEvent) -> Result<()> {
    match key_event.code {
        KeyCode::Up => {
            app.move_selection_up();
            // Update scroll offset if needed (boards view uses List widget with automatic scrolling)
        }
        KeyCode::Down => {
            app.move_selection_down(app.boards.len());
            // Update scroll offset if needed (boards view uses List widget with automatic scrolling)
        }
        KeyCode::Enter => {
            if let Some(board) = app.boards.get(app.selected_index) {
                let board_code = board.code.clone();
                app.state = AppState::Threads(board_code.clone());
                app.load_threads(&board_code).await?;
            }
        }
        KeyCode::Char('r') => {
            app.load_boards().await?;
        }
        _ => {}
    }

    Ok(())
}

async fn handle_threads_keys(app: &mut App, key_event: KeyEvent, board_code: &str) -> Result<()> {
    match key_event.code {
        KeyCode::Up => {
            app.move_selection_up();
            // Thread list uses List widget with automatic scrolling
        }
        KeyCode::Down => {
            app.move_selection_down(app.threads.len());
            // Thread list uses List widget with automatic scrolling
        }
        KeyCode::Enter => {
            if let Some(thread) = app.threads.get(app.selected_index) {
                let thread_id = thread.id;
                app.state = AppState::ThreadDetail(board_code.to_string(), thread_id);
                app.load_thread_detail(board_code, thread_id).await?;
            }
        }
        KeyCode::Char('b') => {
            app.state = AppState::Boards;
        }
        KeyCode::Char('n') => {
            app.state = AppState::CreateThread(board_code.to_string());
            app.active_input = ActiveInput::ThreadTitle;
        }
        KeyCode::Char('t') => {
            app.load_threads(board_code).await?;
        }
        _ => {}
    }

    Ok(())
}

async fn handle_thread_detail_keys(app: &mut App, key_event: KeyEvent, board_code: &str, thread_id: u32) -> Result<()> {
    match key_event.code {
        KeyCode::Up => {
            if app.selected_index > 0 {
                app.selected_index -= 1;
            }
        }
        KeyCode::Down => {
            let flattened_posts = app.get_flattened_posts();
            if app.selected_index < flattened_posts.len().saturating_sub(1) {
                app.selected_index += 1;
            }
        }
        KeyCode::Char('b') => {
            app.state = AppState::Boards;
            app.reset_selection();
        }
        KeyCode::Char('t') => {
            app.state = AppState::Threads(board_code.to_string());
            app.reset_selection();
            app.load_threads(board_code).await?;
        }
        KeyCode::Char('r') => {
            // Reply to thread (no parent)
            app.selected_post_id = None;
            app.state = AppState::CreateReply(board_code.to_string(), thread_id);
            app.active_input = ActiveInput::ReplyContent;
        }
        KeyCode::Char('R') => {
            // Reply to selected post
            let flattened_posts = app.get_flattened_posts();
            if let Some((post, _, _)) = flattened_posts.get(app.selected_index) {
                app.selected_post_id = Some(post.id);
                app.state = AppState::CreateReply(board_code.to_string(), thread_id);
                app.active_input = ActiveInput::ReplyContent;
            }
        }
        _ => {}
    }

    Ok(())
}

async fn handle_create_thread_keys(app: &mut App, key_event: KeyEvent, board_code: &str) -> Result<()> {
    debug!("handle_create_thread_keys: key={:?}, modifiers={:?}", key_event.code, key_event.modifiers);
    match key_event.code {
        KeyCode::Tab => {
            app.active_input = match app.active_input {
                ActiveInput::ThreadTitle => ActiveInput::ThreadContent,
                ActiveInput::ThreadContent => ActiveInput::ThreadTitle,
                _ => ActiveInput::ThreadTitle,
            };
        }
        KeyCode::Char('s') if key_event.modifiers.contains(KeyModifiers::CONTROL) => {
            debug!("Ctrl+S detected, creating thread");
            app.create_thread(board_code).await?;
        }
        KeyCode::Enter if key_event.modifiers.contains(KeyModifiers::CONTROL) => {
            debug!("Ctrl+Enter detected, creating thread");
            app.create_thread(board_code).await?;
        }
        _ => {
            debug!("Delegating to handle_input_keys");
            handle_input_keys(app, key_event);
        }
    }

    Ok(())
}

async fn handle_create_reply_keys(app: &mut App, key_event: KeyEvent, board_code: &str, thread_id: u32) -> Result<()> {
    debug!("handle_create_reply_keys: key={:?}, modifiers={:?}", key_event.code, key_event.modifiers);
    match key_event.code {
        KeyCode::Char('s') if key_event.modifiers.contains(KeyModifiers::CONTROL) => {
            debug!("Ctrl+S detected, creating reply");
            app.create_reply(board_code, thread_id).await?;
        }
        KeyCode::Enter if key_event.modifiers.contains(KeyModifiers::CONTROL) => {
            debug!("Ctrl+Enter detected, creating reply");
            app.create_reply(board_code, thread_id).await?;
        }
        _ => {
            debug!("Delegating to handle_input_keys");
            handle_input_keys(app, key_event);
        }
    }

    Ok(())
}

fn handle_input_keys(app: &mut App, key_event: KeyEvent) {
    if let Some(input) = app.get_active_input_mut() {
        match key_event.code {
            KeyCode::Char(c) => {
                input.insert_char(c);
            }
            KeyCode::Backspace => {
                input.delete_char();
            }
            KeyCode::Left => {
                input.move_cursor_left();
            }
            KeyCode::Right => {
                input.move_cursor_right();
            }
            _ => {}
        }
    }
}
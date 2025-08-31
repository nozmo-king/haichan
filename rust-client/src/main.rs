mod api;
mod app;
mod event;
mod ui;

use anyhow::Result;
use app::App;
use crossterm::{
    event::{DisableMouseCapture, EnableMouseCapture, Event},
    execute,
    terminal::{disable_raw_mode, enable_raw_mode, EnterAlternateScreen, LeaveAlternateScreen},
};
use ratatui::{
    backend::CrosstermBackend,
    Terminal,
};
use std::{
    io::{self, Write},
    time::{Duration, Instant},
};
use log::error;

fn setup_logging() -> Result<()> {
    use fern::Dispatch;
    use chrono::Local;
    use std::fs;
    
    // Create logs directory if it doesn't exist
    fs::create_dir_all("logs")?;
    
    // Write a startup message to verify logging works
    let log_msg = format!("=== Forum TUI started at {} ===\n", Local::now().format("%Y-%m-%d %H:%M:%S"));
    std::fs::OpenOptions::new()
        .create(true)
        .append(true)
        .open("logs/forum-tui.log")?
        .write_all(log_msg.as_bytes())?;
    
    Dispatch::new()
        .format(|out, message, record| {
            out.finish(format_args!(
                "{}[{}][{}] {}",
                Local::now().format("%Y-%m-%d %H:%M:%S%.3f"),
                record.target(),
                record.level(),
                message
            ))
        })
        .level(log::LevelFilter::Debug)
        .chain(fern::log_file("logs/forum-tui.log")?) // Log ONLY to file
        .apply()?;
    
    log::info!("Logging system initialized");
    Ok(())
}

#[tokio::main]
async fn main() -> Result<()> {
    if let Err(e) = setup_logging() {
        eprintln!("Failed to setup logging: {}", e);
        return Err(e);
    }
    // Setup terminal
    enable_raw_mode()?;
    let mut stdout = io::stdout();
    execute!(stdout, EnterAlternateScreen, EnableMouseCapture)?;
    let backend = CrosstermBackend::new(stdout);
    let mut terminal = Terminal::new(backend)?;

    // Create app and run it
    let app = App::new();
    let res = run_app(&mut terminal, app).await;

    // Restore terminal
    disable_raw_mode()?;
    execute!(
        terminal.backend_mut(),
        LeaveAlternateScreen,
        DisableMouseCapture
    )?;
    terminal.show_cursor()?;

    if let Err(err) = res {
        error!("Application error: {:?}", err);
        println!("{err:?}");
    }

    Ok(())
}

async fn run_app<B: ratatui::backend::Backend>(
    terminal: &mut Terminal<B>,
    mut app: App,
) -> Result<()> {
    let tick_rate = Duration::from_millis(250);
    let mut last_tick = Instant::now();

    loop {
        terminal.draw(|f| ui::render(f, &app))?;


        if crossterm::event::poll(Duration::from_millis(0))? {
            if let Event::Key(key) = crossterm::event::read()? {
                if let Err(e) = event::handle_key_event(&mut app, key).await {
                    error!("Error handling key event: {:?}", e);
                    return Err(e);
                }
            }
        }

        if last_tick.elapsed() >= tick_rate {
            last_tick = Instant::now();
        }

        if app.should_quit {
            return Ok(());
        }
    }
}

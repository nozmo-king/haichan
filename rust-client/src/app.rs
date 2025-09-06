use crate::api::{ApiClient, Board, Post, Thread, ThreadDetail, User};
use anyhow::Result;
use log::{error, info, warn};

#[derive(Debug, Clone, PartialEq)]
pub enum AppState {
    Login,
    Boards,
    Threads(String), // board code
    ThreadDetail(String, u32), // board code, thread id
    CreateThread(String), // board code
    CreateReply(String, u32), // board code, thread id
    Help,
}

#[derive(Debug, Clone)]
pub struct InputState {
    pub content: String,
    pub cursor: usize,
}

impl InputState {
    pub fn new() -> Self {
        Self {
            content: String::new(),
            cursor: 0,
        }
    }

    pub fn clear(&mut self) {
        self.content.clear();
        self.cursor = 0;
    }

    pub fn insert_char(&mut self, c: char) {
        self.content.insert(self.cursor, c);
        self.cursor += 1;
    }

    pub fn delete_char(&mut self) {
        if self.cursor > 0 {
            self.cursor -= 1;
            self.content.remove(self.cursor);
        }
    }

    pub fn move_cursor_left(&mut self) {
        if self.cursor > 0 {
            self.cursor -= 1;
        }
    }

    pub fn move_cursor_right(&mut self) {
        if self.cursor < self.content.len() {
            self.cursor += 1;
        }
    }
}

pub struct App {
    pub api_client: ApiClient,
    pub state: AppState,
    pub user: Option<User>,
    pub boards: Vec<Board>,
    pub threads: Vec<Thread>,
    pub thread_detail: Option<ThreadDetail>,
    pub selected_index: usize,
    pub selected_post_id: Option<u32>, // For selecting which post to reply to
    pub status_message: String,
    pub error_message: Option<String>,
    
    // Input fields
    pub private_key_input: InputState,
    
    // Thread creation inputs
    pub thread_title_input: InputState,
    pub thread_content_input: InputState,
    pub reply_content_input: InputState,
    
    // Active input field
    pub active_input: ActiveInput,
    
    
    pub should_quit: bool,
}

#[derive(Debug, Clone, PartialEq)]
pub enum ActiveInput {
    PrivateKey,
    ThreadTitle,
    ThreadContent,
    ReplyContent,
    None,
}

impl App {
    pub fn new() -> Self {
        Self {
            api_client: ApiClient::new("http://localhost:8000/api".to_string()),
            state: AppState::Login,
            user: None,
            boards: Vec::new(),
            threads: Vec::new(),
            thread_detail: None,
            selected_index: 0,
            selected_post_id: None,
            status_message: "Welcome to Forum TUI - Press 'h' for help".to_string(),
            error_message: None,
            
            private_key_input: InputState::new(),
            
            thread_title_input: InputState::new(),
            thread_content_input: InputState::new(),
            reply_content_input: InputState::new(),
            
            active_input: ActiveInput::PrivateKey,
            should_quit: false,
        }
    }

    pub fn quit(&mut self) {
        self.should_quit = true;
    }

    pub fn set_status(&mut self, message: String) {
        self.status_message = message;
        self.error_message = None;
    }

    pub fn set_error(&mut self, message: String) {
        self.error_message = Some(message);
    }


    pub fn move_selection_up(&mut self) {
        if self.selected_index > 0 {
            self.selected_index -= 1;
        }
    }

    pub fn move_selection_down(&mut self, max_items: usize) {
        if self.selected_index < max_items.saturating_sub(1) {
            self.selected_index += 1;
        }
    }

    pub fn reset_selection(&mut self) {
        self.selected_index = 0;
    }


    pub async fn login(&mut self) -> Result<()> {
        if self.private_key_input.content.is_empty() {
            warn!("Login attempted with empty private key");
            self.set_error("Please enter your private key".to_string());
            return Ok(());
        }

        info!("Starting login process");
        self.set_status("Logging in...".to_string());
        
        match self.api_client.login_with_private_key(&self.private_key_input.content).await {
            Ok(response) => {
                info!("Login successful, user ID: {}", response.user.id);
                self.api_client.set_token(response.token);
                self.user = Some(response.user);
                self.state = AppState::Boards;
                self.active_input = ActiveInput::None;
                self.set_status("Login successful!".to_string());
                self.load_boards().await?;
            }
            Err(e) => {
                error!("Login failed: {}", e);
                self.set_error(format!("Login failed: {}", e));
            }
        }
        
        Ok(())
    }

    pub async fn load_boards(&mut self) -> Result<()> {
        self.set_status("Loading boards...".to_string());
        
        match self.api_client.get_boards().await {
            Ok(boards) => {
                self.boards = boards;
                self.reset_selection();
                self.set_status(format!("Loaded {} boards", self.boards.len()));
            }
            Err(e) => {
                self.set_error(format!("Failed to load boards: {}", e));
            }
        }
        
        Ok(())
    }

    pub async fn load_threads(&mut self, board_code: &str) -> Result<()> {
        self.set_status("Loading threads...".to_string());
        
        match self.api_client.get_threads(board_code, None).await {
            Ok(response) => {
                self.threads = response.threads;
                self.reset_selection();
                self.set_status(format!("Loaded {} threads", self.threads.len()));
            }
            Err(e) => {
                self.set_error(format!("Failed to load threads: {}", e));
            }
        }
        
        Ok(())
    }

    pub async fn load_thread_detail(&mut self, board_code: &str, thread_id: u32) -> Result<()> {
        self.set_status("Loading thread...".to_string());
        
        match self.api_client.get_thread(board_code, thread_id).await {
            Ok(detail) => {
                self.thread_detail = Some(detail);
                self.reset_selection();
                self.set_status("Thread loaded".to_string());
            }
            Err(e) => {
                self.set_error(format!("Failed to load thread: {}", e));
            }
        }
        
        Ok(())
    }

    pub async fn create_thread(&mut self, board_code: &str) -> Result<()> {
        if self.thread_title_input.content.is_empty() || self.thread_content_input.content.is_empty() {
            self.set_error("Please fill in both title and content".to_string());
            return Ok(());
        }

        self.set_status("Creating thread...".to_string());
        
        match self.api_client.create_thread(
            board_code,
            &self.thread_title_input.content,
            &self.thread_content_input.content,
        ).await {
            Ok(_) => {
                self.thread_title_input.clear();
                self.thread_content_input.clear();
                self.state = AppState::Threads(board_code.to_string());
                self.active_input = ActiveInput::None;
                self.set_status("Thread created successfully!".to_string());
                self.load_threads(board_code).await?;
            }
            Err(e) => {
                self.set_error(format!("Failed to create thread: {}", e));
            }
        }
        
        Ok(())
    }

    pub async fn create_reply(&mut self, board_code: &str, thread_id: u32) -> Result<()> {
        if self.reply_content_input.content.is_empty() {
            self.set_error("Please enter reply content".to_string());
            return Ok(());
        }

        self.set_status("Creating reply...".to_string());
        
        match self.api_client.create_reply(
            board_code,
            thread_id,
            &self.reply_content_input.content,
            self.selected_post_id,
        ).await {
            Ok(_) => {
                self.reply_content_input.clear();
                self.selected_post_id = None;
                self.state = AppState::ThreadDetail(board_code.to_string(), thread_id);
                self.active_input = ActiveInput::None;
                self.set_status("Reply created successfully!".to_string());
                self.load_thread_detail(board_code, thread_id).await?;
            }
            Err(e) => {
                self.set_error(format!("Failed to create reply: {}", e));
            }
        }
        
        Ok(())
    }

    pub fn get_active_input_mut(&mut self) -> Option<&mut InputState> {
        match self.active_input {
            ActiveInput::PrivateKey => Some(&mut self.private_key_input),
            ActiveInput::ThreadTitle => Some(&mut self.thread_title_input),
            ActiveInput::ThreadContent => Some(&mut self.thread_content_input),
            ActiveInput::ReplyContent => Some(&mut self.reply_content_input),
            ActiveInput::None => None,
        }
    }

    // Helper function to get posts sorted by conversation flow and timestamps
    pub fn get_flattened_posts(&self) -> Vec<(Post, String, Option<u32>)> {
        if let Some(detail) = &self.thread_detail {
            // Sort posts by timestamp within their reply structure for better conversation flow
            let sorted_posts = Self::sort_posts_by_conversation(&detail.posts);
            Self::flatten_posts_with_tree_prefix(&sorted_posts, String::new(), true, None)
        } else {
            Vec::new()
        }
    }

    // Sort posts recursively by timestamps to improve conversation flow
    pub fn sort_posts_by_conversation(posts: &[Post]) -> Vec<Post> {
        let mut sorted_posts: Vec<Post> = posts.to_vec();
        
        // Sort posts at this level by timestamp
        sorted_posts.sort_by(|a, b| a.created_at.cmp(&b.created_at));
        
        // Recursively sort replies within each post
        sorted_posts = sorted_posts.into_iter().map(|mut post| {
            post.replies = Self::sort_posts_by_conversation(&post.replies);
            post
        }).collect();
        
        sorted_posts
    }

    pub fn flatten_posts_with_tree_prefix(posts: &[Post], parent_prefix: String, is_root: bool, parent_id: Option<u32>) -> Vec<(Post, String, Option<u32>)> {
        let mut result = Vec::new();
        for (i, post) in posts.iter().enumerate() {
            let is_last = i == posts.len() - 1;
            
            // Build the tree prefix for this post
            let tree_prefix = if is_root {
                String::new()
            } else if is_last {
                format!("{}└─ ", parent_prefix)
            } else {
                format!("{}├─ ", parent_prefix)
            };
            
            result.push(((*post).clone(), tree_prefix.clone(), parent_id));
            
            // Recursively add nested replies with updated prefix
            if !post.replies.is_empty() {
                let child_prefix = if is_root {
                    // Direct replies to root posts start with no base indentation
                    String::new()
                } else if is_last {
                    // This post is the last in its group, so no vertical line continuation
                    format!("{}   ", parent_prefix)
                } else {
                    // This post has siblings below it, so continue the vertical line
                    format!("{}│  ", parent_prefix)
                };
                
                let nested = Self::flatten_posts_with_tree_prefix(&post.replies, child_prefix, false, Some(post.id));
                result.extend(nested);
            }
        }
        result
    }
}
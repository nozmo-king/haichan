use anyhow::Result;
use reqwest::Client;
use serde::{Deserialize, Serialize};
use chrono::{DateTime, Utc};
use log::{debug, error, info};

#[derive(Clone)]
pub struct ApiClient {
    client: Client,
    base_url: String,
    token: Option<String>,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct User {
    pub id: u32,
    pub public_key: String,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct Board {
    pub id: u32,
    pub name: String,
    pub code: String,
    pub description: String,
    pub threads_count: u32,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct Thread {
    pub id: u32,
    pub title: String,
    pub content: String,
    pub author_name: String,
    #[serde(default)]
    pub posts_count: u32,
    pub created_at: DateTime<Utc>,
    pub image_path: Option<String>,
    pub image_filename: Option<String>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Post {
    pub id: u32,
    pub content: String,
    pub author_name: String,
    pub created_at: DateTime<Utc>,
    pub image_path: Option<String>,
    pub image_filename: Option<String>,
    #[serde(default)]
    pub replies: Vec<Post>,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct ThreadDetail {
    pub thread: Thread,
    pub posts: Vec<Post>,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct ChallengeResponse {
    pub challenge: String,
    pub user_id: u32,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct LoginResponse {
    pub token: String,
    pub user: User,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct BoardsResponse {
    pub boards: Vec<Board>,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct ThreadsResponse {
    pub threads: Vec<Thread>,
    pub pagination: Pagination,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct Pagination {
    pub current_page: u32,
    pub last_page: u32,
    pub per_page: u32,
    pub total: u32,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct ErrorResponse {
    pub error: String,
}

impl ApiClient {
    pub fn new(base_url: String) -> Self {
        Self {
            client: Client::new(),
            base_url,
            token: None,
        }
    }

    pub fn set_token(&mut self, token: String) {
        self.token = Some(token);
    }

    pub fn is_authenticated(&self) -> bool {
        self.token.is_some()
    }

    fn get_headers(&self) -> reqwest::header::HeaderMap {
        let mut headers = reqwest::header::HeaderMap::new();
        headers.insert("Content-Type", "application/json".parse().unwrap());
        
        if let Some(token) = &self.token {
            headers.insert(
                "Authorization",
                format!("Bearer {}", token).parse().unwrap(),
            );
        }
        
        headers
    }

    pub async fn login_with_private_key(&self, private_key: &str) -> Result<LoginResponse> {
        use secp256k1::{Secp256k1, SecretKey, Message};
        use sha2::{Sha256, Digest};
        
        let secp = Secp256k1::new();
        let secret_key = SecretKey::from_slice(&hex::decode(private_key)?)?;
        let public_key = secret_key.public_key(&secp);
        let public_key_hex = hex::encode(public_key.serialize());
        
        // First get challenge
        let challenge_response = self.get_challenge(&public_key_hex).await?;
        
        // Sign the challenge
        let challenge_hash = Sha256::digest(challenge_response.challenge.as_bytes());
        let message = Message::from_digest_slice(&challenge_hash)?;
        let signature = secp.sign_ecdsa(&message, &secret_key);
        let signature_hex = hex::encode(&signature.serialize_compact());
        
        // Login with signature
        self.login(&signature_hex, &challenge_response.challenge, challenge_response.user_id).await
    }

    async fn get_challenge(&self, public_key: &str) -> Result<ChallengeResponse> {
        let body = serde_json::json!({
            "public_key": public_key
        });

        debug!("Requesting challenge for public key: {}", public_key);
        let response = self
            .client
            .post(&format!("{}/auth/challenge", self.base_url))
            .headers(self.get_headers())
            .json(&body)
            .send()
            .await?;

        let status = response.status();
        debug!("Challenge response status: {}", status);

        if status.is_success() {
            match response.json().await {
                Ok(challenge) => {
                    info!("Challenge received successfully");
                    Ok(challenge)
                }
                Err(e) => {
                    error!("Failed to parse challenge response as JSON: {}", e);
                    Err(anyhow::anyhow!("Error decoding challenge response body: {}", e))
                }
            }
        } else {
            let response_text = response.text().await?;
            error!("Challenge request failed with status {}: {}", status, response_text);
            
            if let Ok(error_response) = serde_json::from_str::<ErrorResponse>(&response_text) {
                Err(anyhow::anyhow!("API Error ({}): {}", status, error_response.error))
            } else {
                Err(anyhow::anyhow!("HTTP Error {}: {}", status, response_text))
            }
        }
    }

    async fn login(&self, signature: &str, challenge: &str, user_id: u32) -> Result<LoginResponse> {
        let body = serde_json::json!({
            "signature": signature,
            "challenge": challenge,
            "user_id": user_id
        });

        debug!("Attempting login for user_id: {}", user_id);
        let response = self
            .client
            .post(&format!("{}/auth/login", self.base_url))
            .headers(self.get_headers())
            .json(&body)
            .send()
            .await?;

        let status = response.status();
        debug!("Login response status: {}", status);

        if status.is_success() {
            match response.json().await {
                Ok(login_response) => {
                    info!("Login successful for user_id: {}", user_id);
                    Ok(login_response)
                }
                Err(e) => {
                    error!("Failed to parse login response as JSON: {}", e);
                    Err(anyhow::anyhow!("Error decoding login response body: {}", e))
                }
            }
        } else {
            let response_text = response.text().await?;
            error!("Login failed with status {}: {}", status, response_text);
            
            if let Ok(error_response) = serde_json::from_str::<ErrorResponse>(&response_text) {
                Err(anyhow::anyhow!("API Error ({}): {}", status, error_response.error))
            } else {
                Err(anyhow::anyhow!("HTTP Error {}: {}", status, response_text))
            }
        }
    }

    pub async fn logout(&self) -> Result<()> {
        let response = self
            .client
            .post(&format!("{}/auth/logout", self.base_url))
            .headers(self.get_headers())
            .send()
            .await?;

        if response.status().is_success() {
            Ok(())
        } else {
            let error: ErrorResponse = response.json().await?;
            Err(anyhow::anyhow!("API Error: {}", error.error))
        }
    }

    pub async fn get_boards(&self) -> Result<Vec<Board>> {
        debug!("Requesting boards from {}/boards", self.base_url);
        let response = self
            .client
            .get(&format!("{}/boards", self.base_url))
            .headers(self.get_headers())
            .send()
            .await?;

        let status = response.status();
        debug!("Boards response status: {}", status);

        if status.is_success() {
            match response.json().await {
                Ok(boards_response) => {
                    let boards_response: BoardsResponse = boards_response;
                    info!("Successfully loaded {} boards", boards_response.boards.len());
                    Ok(boards_response.boards)
                }
                Err(e) => {
                    error!("Failed to parse boards response as JSON: {}", e);
                    Err(anyhow::anyhow!("Error decoding boards response body: {}", e))
                }
            }
        } else {
            let response_text = response.text().await?;
            error!("Get boards failed with status {}: {}", status, response_text);
            
            if let Ok(error_response) = serde_json::from_str::<ErrorResponse>(&response_text) {
                Err(anyhow::anyhow!("API Error ({}): {}", status, error_response.error))
            } else {
                Err(anyhow::anyhow!("HTTP Error {}: {}", status, response_text))
            }
        }
    }

    pub async fn get_threads(&self, board_code: &str, page: Option<u32>) -> Result<ThreadsResponse> {
        let mut url = format!("{}/boards/{}/threads", self.base_url, board_code);
        if let Some(page) = page {
            url = format!("{}?page={}", url, page);
        }

        let response = self
            .client
            .get(&url)
            .headers(self.get_headers())
            .send()
            .await?;

        if response.status().is_success() {
            Ok(response.json().await?)
        } else {
            let error: ErrorResponse = response.json().await?;
            Err(anyhow::anyhow!("API Error: {}", error.error))
        }
    }

    pub async fn get_thread(&self, board_code: &str, thread_id: u32) -> Result<ThreadDetail> {
        debug!("Requesting thread {} from board {}", thread_id, board_code);
        let response = self
            .client
            .get(&format!("{}/boards/{}/threads/{}", self.base_url, board_code, thread_id))
            .headers(self.get_headers())
            .send()
            .await?;

        let status = response.status();
        debug!("Thread response status: {}", status);

        if status.is_success() {
            let response_text = response.text().await?;
            debug!("Thread response body: {}", response_text);
            
            match serde_json::from_str::<ThreadDetail>(&response_text) {
                Ok(thread_detail) => {
                    info!("Successfully loaded thread {} with {} posts", thread_id, thread_detail.posts.len());
                    Ok(thread_detail)
                }
                Err(e) => {
                    error!("Failed to parse thread response as JSON: {}", e);
                    error!("Response was: {}", response_text);
                    Err(anyhow::anyhow!("Error decoding thread response body: {}", e))
                }
            }
        } else {
            let response_text = response.text().await?;
            error!("Get thread failed with status {}: {}", status, response_text);
            
            if let Ok(error_response) = serde_json::from_str::<ErrorResponse>(&response_text) {
                Err(anyhow::anyhow!("API Error ({}): {}", status, error_response.error))
            } else {
                Err(anyhow::anyhow!("HTTP Error {}: {}", status, response_text))
            }
        }
    }

    pub async fn create_thread(&self, board_code: &str, title: &str, content: &str) -> Result<Thread> {
        let body = serde_json::json!({
            "title": title,
            "content": content
        });

        let response = self
            .client
            .post(&format!("{}/boards/{}/threads", self.base_url, board_code))
            .headers(self.get_headers())
            .json(&body)
            .send()
            .await?;

        if response.status().is_success() {
            let response_json: serde_json::Value = response.json().await?;
            Ok(serde_json::from_value(response_json["thread"].clone())?)
        } else {
            let status = response.status();
            let response_text = response.text().await?;
            error!("Create thread failed with status {}: {}", status, response_text);
            
            if let Ok(error_response) = serde_json::from_str::<ErrorResponse>(&response_text) {
                Err(anyhow::anyhow!("API Error ({}): {}", status, error_response.error))
            } else {
                Err(anyhow::anyhow!("HTTP Error {}: {}", status, response_text))
            }
        }
    }

    pub async fn create_reply(&self, board_code: &str, thread_id: u32, content: &str, parent_id: Option<u32>) -> Result<Post> {
        let mut body = serde_json::json!({
            "content": content
        });

        if let Some(parent_id) = parent_id {
            body["parent_id"] = serde_json::json!(parent_id);
        }

        let response = self
            .client
            .post(&format!("{}/boards/{}/threads/{}/replies", self.base_url, board_code, thread_id))
            .headers(self.get_headers())
            .json(&body)
            .send()
            .await?;

        if response.status().is_success() {
            let response_json: serde_json::Value = response.json().await?;
            Ok(serde_json::from_value(response_json["post"].clone())?)
        } else {
            let status = response.status();
            let response_text = response.text().await?;
            error!("Create reply failed with status {}: {}", status, response_text);
            
            if let Ok(error_response) = serde_json::from_str::<ErrorResponse>(&response_text) {
                Err(anyhow::anyhow!("API Error ({}): {}", status, error_response.error))
            } else {
                Err(anyhow::anyhow!("HTTP Error {}: {}", status, response_text))
            }
        }
    }
}
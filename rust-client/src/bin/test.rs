use forum_tui_rust::api::ApiClient;

#[tokio::main]
async fn main() {
    println!("Testing API client...");
    
    let client = ApiClient::new("http://localhost:8000/api".to_string());
    println!("✓ API client created");
    
    // Test if we can make a request (will fail without server running, but tests the client setup)
    match client.get_boards().await {
        Ok(_) => println!("✓ API connection successful"),
        Err(e) => println!("⚠ API connection failed (expected without running server): {}", e),
    }
    
    println!("Rust TUI client is ready to use!");
}
#[cfg(test)]
mod tests {
    use super::*;
    use chrono::Utc;
    use crate::api::Post;
    use crate::app::App;

    #[test]
    fn test_tree_structure() {
        use std::time::Duration;
        
        let base_time = Utc::now();
        
        // Create test posts with nested replies and realistic timestamps
        let post1 = Post {
            id: 1,
            content: "Post 1".to_string(),
            author_name: "User1".to_string(),
            created_at: base_time,
            image_path: None,
            image_filename: None,
            replies: vec![
                Post {
                    id: 2,
                    content: "Reply to Post 1".to_string(),
                    author_name: "User2".to_string(),
                    created_at: base_time + chrono::Duration::minutes(1),
                    image_path: None,
                    image_filename: None,
                    replies: vec![
                        Post {
                            id: 3,
                            content: "Reply to Reply".to_string(),
                            author_name: "User3".to_string(),
                            created_at: base_time + chrono::Duration::minutes(2),
                            image_path: None,
                            image_filename: None,
                            replies: vec![],
                        }
                    ],
                },
                Post {
                    id: 4,
                    content: "Another reply to Post 1".to_string(),
                    author_name: "User4".to_string(),
                    created_at: base_time + chrono::Duration::minutes(3),
                    image_path: None,
                    image_filename: None,
                    replies: vec![],
                }
            ],
        };

        let posts = vec![post1];
        let flattened = App::flatten_posts_with_tree_prefix(&posts, String::new(), true, None);
        
        println!("Tree Structure:");
        for (post, prefix, parent_id) in &flattened {
            let parent_info = if let Some(pid) = parent_id {
                format!(" (replying to #{})", pid)
            } else {
                String::new()
            };
            println!("{}Post {}: {}{}", prefix, post.id, post.content, parent_info);
        }

        // Expected structure:
        // Post 1: Post 1
        // ├─ Post 2: Reply to Post 1 (replying to #1)
        // │  └─ Post 3: Reply to Reply (replying to #2)
        // └─ Post 4: Another reply to Post 1 (replying to #1)
        
        assert_eq!(flattened.len(), 4);
        assert_eq!(flattened[0].1, ""); // Post 1 - no prefix
        assert_eq!(flattened[0].2, None); // Post 1 - no parent
        assert_eq!(flattened[1].1, "├─ "); // Post 2 - first child
        assert_eq!(flattened[1].2, Some(1)); // Post 2 - replying to post 1
        assert_eq!(flattened[2].1, "│  └─ "); // Post 3 - nested reply, indented
        assert_eq!(flattened[2].2, Some(2)); // Post 3 - replying to post 2
        assert_eq!(flattened[3].1, "└─ "); // Post 4 - last child
        assert_eq!(flattened[3].2, Some(1)); // Post 4 - replying to post 1
    }

    #[test]
    fn test_timestamp_sorting() {
        let base_time = Utc::now();
        
        // Create posts where replies were made out of ID order to test timestamp sorting
        let post1 = Post {
            id: 1,
            content: "Original post".to_string(),
            author_name: "User1".to_string(),
            created_at: base_time,
            image_path: None,
            image_filename: None,
            replies: vec![
                // Post 4 was made BEFORE Post 2, so it should appear first despite higher ID
                Post {
                    id: 4,
                    content: "First reply (posted earlier)".to_string(),
                    author_name: "User4".to_string(),
                    created_at: base_time + chrono::Duration::minutes(1),
                    image_path: None,
                    image_filename: None,
                    replies: vec![],
                },
                Post {
                    id: 2,
                    content: "Second reply (posted later)".to_string(),
                    author_name: "User2".to_string(),
                    created_at: base_time + chrono::Duration::minutes(5),
                    image_path: None,
                    image_filename: None,
                    replies: vec![],
                },
            ],
        };

        let posts = vec![post1];
        let sorted_posts = App::sort_posts_by_conversation(&posts);
        let flattened = App::flatten_posts_with_tree_prefix(&sorted_posts, String::new(), true, None);
        
        println!("Timestamp Sorted Structure:");
        for (post, prefix, parent_id) in &flattened {
            let parent_info = if let Some(pid) = parent_id {
                format!(" (replying to #{})", pid)
            } else {
                String::new()
            };
            println!("{}Post {}: {}{}", prefix, post.id, post.content, parent_info);
        }

        // Post 4 should come before Post 2 because it was posted earlier
        assert_eq!(flattened[1].0.id, 4); // First reply should be Post 4
        assert_eq!(flattened[2].0.id, 2); // Second reply should be Post 2
    }

    #[test]
    fn test_deep_nesting_indentation() {
        let base_time = Utc::now();
        
        // Create posts with deeper nesting to test indentation
        let post1 = Post {
            id: 1,
            content: "Root post".to_string(),
            author_name: "User1".to_string(),
            created_at: base_time,
            image_path: None,
            image_filename: None,
            replies: vec![
                Post {
                    id: 2,
                    content: "Level 1 reply".to_string(),
                    author_name: "User2".to_string(),
                    created_at: base_time + chrono::Duration::minutes(1),
                    image_path: None,
                    image_filename: None,
                    replies: vec![
                        Post {
                            id: 3,
                            content: "Level 2 reply".to_string(),
                            author_name: "User3".to_string(),
                            created_at: base_time + chrono::Duration::minutes(2),
                            image_path: None,
                            image_filename: None,
                            replies: vec![
                                Post {
                                    id: 4,
                                    content: "Level 3 reply".to_string(),
                                    author_name: "User4".to_string(),
                                    created_at: base_time + chrono::Duration::minutes(3),
                                    image_path: None,
                                    image_filename: None,
                                    replies: vec![],
                                }
                            ],
                        }
                    ],
                }
            ],
        };

        let posts = vec![post1];
        let sorted_posts = App::sort_posts_by_conversation(&posts);
        let flattened = App::flatten_posts_with_tree_prefix(&sorted_posts, String::new(), true, None);
        
        println!("Deep Nesting Structure:");
        for (post, prefix, parent_id) in &flattened {
            let parent_info = if let Some(pid) = parent_id {
                format!(" (replying to #{})", pid)
            } else {
                String::new()
            };
            println!("{}Post {}: {}{}", prefix, post.id, post.content, parent_info);
        }

        // Check that indentation increases with nesting level
        assert_eq!(flattened[0].1, ""); // Root post - no prefix
        assert_eq!(flattened[1].1, "└─ "); // Level 1 - basic tree
        assert_eq!(flattened[2].1, "   └─ "); // Level 2 - indented
        assert_eq!(flattened[3].1, "      └─ "); // Level 3 - more indented
    }
}
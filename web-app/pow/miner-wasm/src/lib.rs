use wasm_bindgen::prelude::*;
use verifier::{verify_v1};

#[wasm_bindgen]
pub fn mine(challenge_bytes: &[u8], required_prefix_hex: &str, max_iters: u64) -> u64 {
    for nonce in 0..max_iters {
        let mut input_bytes = challenge_bytes.to_vec();
        input_bytes.extend_from_slice(&nonce.to_le_bytes());
        let (is_valid, _) = verify_v1(&input_bytes, required_prefix_hex);
        if is_valid {
            return nonce;
        }
    }
    return u64::MAX;
}

#[wasm_bindgen]
pub fn verify_local(challenge_bytes: &[u8], required_prefix_hex: &str, nonce: u64) -> bool {
    let mut input_bytes = challenge_bytes.to_vec();
    input_bytes.extend_from_slice(&nonce.to_le_bytes());
    let (is_valid, _) = verify_v1(&input_bytes, required_prefix_hex);
    is_valid
}

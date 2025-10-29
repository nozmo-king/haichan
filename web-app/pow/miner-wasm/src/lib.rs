use wasm_bindgen::prelude::*;
use serde::{Deserialize, Serialize};
use js_sys::Date;
use verifier::{verify_v1, MINER_VERSION};

#[derive(Serialize, Deserialize)]
pub struct FoundNonce {
    pub nonce: u64,
    pub timestamp_i64: i64,
    pub hash_hex: String,
}

#[wasm_bindgen]
pub struct Miner {
    challenge_bytes: Vec<u8>,
    required_prefix_hex: String,
}

#[wasm_bindgen]
impl Miner {
    #[wasm_bindgen(constructor)]
    pub fn new() -> Miner {
        Miner {
            challenge_bytes: Vec::new(),
            required_prefix_hex: String::new(),
        }
    }
}

#[wasm_bindgen]
pub fn version() -> u32 {
    1
}

#[wasm_bindgen]
pub fn init(required_prefix_hex: String, challenge_bytes: Vec<u8>) -> Miner {
    Miner {
        challenge_bytes,
        required_prefix_hex,
    }
}

#[wasm_bindgen]
pub fn mine(miner: &Miner, max_iters: u64) -> JsValue {
    for nonce in 0..max_iters {
        let (is_valid, hash_hex) = verify_v1(&miner.challenge_bytes, nonce, &miner.required_prefix_hex);
        if is_valid {
            let timestamp_i64 = Date::now() as i64;
            let found = FoundNonce {
                nonce,
                timestamp_i64,
                hash_hex,
            };
            return serde_wasm_bindgen::to_value(&found).unwrap();
        }
    }
    JsValue::NULL
}

#[wasm_bindgen]
pub fn verify(miner: &Miner, nonce: u64) -> bool {
    let (is_valid, _) = verify_v1(&miner.challenge_bytes, nonce, &miner.required_prefix_hex);
    is_valid
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_version() {
        assert_eq!(version(), 1);
    }

    #[test]
    fn test_miner_creation() {
        let miner = init("21e8".to_string(), b"test".to_vec());
        assert_eq!(miner.required_prefix_hex, "21e8");
        assert_eq!(miner.challenge_bytes, b"test");
    }

    #[test]
    fn test_verify() {
        let miner = init("f".to_string(), b"HC1test".to_vec());
        // This should find a solution within reasonable range
        for nonce in 0..10000 {
            if verify(&miner, nonce) {
                assert!(nonce < 10000, "Found solution with nonce: {}", nonce);
                return;
            }
        }
        panic!("No solution found in range");
    }
}
// Minimal WASM SHA-256 loader stub.
// Expect a compiled module to set window.WasmSha256.hash = async (str)=>hex
(function(){
  if (window.WasmSha256) return;
  window.WasmSha256 = {
    hash: async (str) => {
      // Fallback to SubtleCrypto if real WASM bundle not swapped in
      const enc = new TextEncoder();
      const buf = await crypto.subtle.digest('SHA-256', enc.encode(str));
      return Array.from(new Uint8Array(buf)).map(b=>b.toString(16).padStart(2,'0')).join('');
    }
  };
})();


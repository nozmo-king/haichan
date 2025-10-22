// RIPEMD-160 implementation for Bitcoin address generation
// Based on the specification

function RIPEMD160() {
    const K = [0x00000000, 0x5a827999, 0x6ed9eba1, 0x8f1bbcdc, 0xa953fd4e];
    const Kp = [0x50a28be6, 0x5c4dd124, 0x6d703ef3, 0x7a6d76e9, 0x00000000];
    
    function f(x, y, z, j) {
        if (j < 16) return x ^ y ^ z;
        else if (j < 32) return (x & y) | (~x & z);
        else if (j < 48) return (x | ~y) ^ z;
        else if (j < 64) return (x & z) | (y & ~z);
        else return x ^ (y | ~z);
    }
    
    function rotl(x, n) {
        return (x << n) | (x >>> (32 - n));
    }
    
    function pad(msg) {
        const len = msg.length;
        const bitLen = len * 8;
        const padLen = (len % 64 < 56) ? (56 - len % 64) : (120 - len % 64);
        const pad = new Uint8Array(len + padLen + 8);
        
        pad.set(msg);
        pad[len] = 0x80;
        
        // Length in bits as 64-bit little-endian
        for (let i = 0; i < 8; i++) {
            pad[len + padLen + i] = (bitLen >>> (i * 8)) & 0xff;
        }
        
        return pad;
    }
    
    this.digest = function(msg) {
        const padded = pad(msg);
        const blocks = padded.length / 64;
        
        let h0 = 0x67452301, h1 = 0xefcdab89, h2 = 0x98badcfe, h3 = 0x10325476, h4 = 0xc3d2e1f0;
        
        for (let blk = 0; blk < blocks; blk++) {
            const X = new Array(16);
            const offset = blk * 64;
            
            for (let i = 0; i < 16; i++) {
                X[i] = padded[offset + i * 4] |
                       (padded[offset + i * 4 + 1] << 8) |
                       (padded[offset + i * 4 + 2] << 16) |
                       (padded[offset + i * 4 + 3] << 24);
            }
            
            let al = h0, bl = h1, cl = h2, dl = h3, el = h4;
            let ar = h0, br = h1, cr = h2, dr = h3, er = h4;
            
            // Left line
            const zl = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,
                        7, 4, 13, 1, 10, 6, 15, 3, 12, 0, 9, 5, 2, 14, 11, 8,
                        3, 10, 14, 4, 9, 15, 8, 1, 2, 7, 0, 6, 13, 11, 5, 12,
                        1, 9, 11, 10, 0, 8, 12, 4, 13, 3, 7, 15, 14, 5, 6, 2,
                        4, 0, 5, 9, 7, 12, 2, 10, 14, 1, 3, 8, 11, 6, 15, 13];
                        
            const sl = [11, 14, 15, 12, 5, 8, 7, 9, 11, 13, 14, 15, 6, 7, 9, 8,
                        7, 6, 8, 13, 11, 9, 7, 15, 7, 12, 15, 9, 11, 7, 13, 12,
                        11, 13, 6, 7, 14, 9, 13, 15, 14, 8, 13, 6, 5, 12, 7, 5,
                        11, 12, 14, 15, 14, 15, 9, 8, 9, 14, 5, 6, 8, 6, 5, 12,
                        9, 15, 5, 11, 6, 8, 13, 12, 5, 12, 13, 14, 11, 8, 5, 6];
                        
            // Right line  
            const zr = [5, 14, 7, 0, 9, 2, 11, 4, 13, 6, 15, 8, 1, 10, 3, 12,
                        6, 11, 3, 7, 0, 13, 5, 10, 14, 15, 8, 12, 4, 9, 1, 2,
                        15, 5, 1, 3, 7, 14, 6, 9, 11, 8, 12, 2, 10, 0, 4, 13,
                        8, 6, 4, 1, 3, 11, 15, 0, 5, 12, 2, 13, 9, 7, 10, 14,
                        12, 15, 10, 4, 1, 5, 8, 7, 6, 2, 13, 14, 0, 3, 9, 11];
                        
            const sr = [8, 9, 9, 11, 13, 15, 15, 5, 7, 7, 8, 11, 14, 14, 12, 6,
                        9, 13, 15, 7, 12, 8, 9, 11, 7, 7, 12, 7, 6, 15, 13, 11,
                        9, 7, 15, 11, 8, 6, 6, 14, 12, 13, 5, 14, 13, 13, 7, 5,
                        15, 5, 8, 11, 14, 14, 6, 14, 6, 9, 12, 9, 12, 5, 15, 8,
                        8, 5, 12, 9, 12, 5, 14, 6, 8, 13, 6, 5, 15, 13, 11, 11];
            
            for (let j = 0; j < 80; j++) {
                const t = (al + f(bl, cl, dl, j) + X[zl[j]] + K[Math.floor(j / 16)]) >>> 0;
                const tl = (rotl(t, sl[j]) + el) >>> 0;
                al = el; el = dl; dl = rotl(cl, 10); cl = bl; bl = tl;
                
                const s = (ar + f(br, cr, dr, 79 - j) + X[zr[j]] + Kp[Math.floor(j / 16)]) >>> 0;
                const tr = (rotl(s, sr[j]) + er) >>> 0;
                ar = er; er = dr; dr = rotl(cr, 10); cr = br; br = tr;
            }
            
            const t = (h1 + cl + dr) >>> 0;
            h1 = (h2 + dl + er) >>> 0;
            h2 = (h3 + el + ar) >>> 0;
            h3 = (h4 + al + br) >>> 0;
            h4 = (h0 + bl + cr) >>> 0;
            h0 = t;
        }
        
        const result = new Uint8Array(20);
        for (let i = 0; i < 5; i++) {
            const h = [h0, h1, h2, h3, h4][i];
            result[i * 4] = h & 0xff;
            result[i * 4 + 1] = (h >>> 8) & 0xff;
            result[i * 4 + 2] = (h >>> 16) & 0xff;
            result[i * 4 + 3] = (h >>> 24) & 0xff;
        }
        
        return result;
    };
}

window.RIPEMD160 = RIPEMD160;
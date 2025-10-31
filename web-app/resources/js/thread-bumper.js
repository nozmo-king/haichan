class ThreadBumper {
    constructor(threadId, boardName) {
        this.threadId = threadId;
        this.boardName = boardName;
        this.isMining = false;
        this.totalHashes = 0;
        this.nonce = Math.floor(Math.random() * 1000000);
        this.targetPattern = '21e8';
        this.startTime = null;
        this.points = 0;

        document.getElementById('startBumpMining').onclick = () => this.startMining();
        document.getElementById('stopBumpMining').onclick = () => this.stopMining();
        document.getElementById('bumpDifficulty').onchange = (e) => {
            this.targetPattern = e.target.value;
        };

        setInterval(() => this.updateDisplay(), 1000);
    }

    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async mine() {
        const baseData = `Bump_${this.threadId}_${Date.now()}`;

        while (this.isMining) {
            const data = `${baseData}_${this.nonce}`;
            const hash = await this.sha256(data);

            this.totalHashes++;
            this.nonce++;

            if (hash.toLowerCase().includes(this.targetPattern.toLowerCase())) {
                await this.submitBump({
                    hash: hash,
                    nonce: this.nonce - 1,
                    data: data,
                    pattern: this.targetPattern
                });
                break;
            }

            if (this.totalHashes % 1000 === 0) {
                await new Promise(r => setTimeout(r, 1));
            }
        }
    }

    async submitBump(proof) {
        try {
            const response = await fetch(`/api/${this.boardName}/thread/${this.threadId}/bump`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(proof)
            });

            const result = await response.json();

            if (result.success) {
                this.points += result.points;
                alert(`Thread bumped! +${result.points} points`);
            } else {
                alert(`Failed: ${result.message}`);
            }
        } catch (error) {
            alert(`Error: ${error.message}`);
        }

        this.stopMining();
    }

    startMining() {
        this.isMining = true;
        this.startTime = Date.now();
        document.getElementById('startBumpMining').disabled = true;
        document.getElementById('stopBumpMining').disabled = false;
        this.mine();
    }

    stopMining() {
        this.isMining = false;
        document.getElementById('startBumpMining').disabled = false;
        document.getElementById('stopBumpMining').disabled = true;
    }

    updateDisplay() {
        if (this.isMining && this.startTime) {
            const elapsed = (Date.now() - this.startTime) / 1000;
            const hashrate = Math.round(this.totalHashes / Math.max(elapsed, 1));
            document.getElementById('bumpHashrate').textContent = hashrate;
            document.getElementById('bumpHashes').textContent = this.totalHashes;
            document.getElementById('bumpPoints').textContent = this.points;
        }
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const threadElement = document.querySelector('[data-thread-id]');
    if (threadElement) {
        const threadId = threadElement.dataset.threadId;
        const boardName = threadElement.dataset.boardCode;
        window.bumper = new ThreadBumper(threadId, boardName);
    }
});

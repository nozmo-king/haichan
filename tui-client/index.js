const blessed = require('blessed');
const axios = require('axios');
const inquirer = require('inquirer');
const chalk = require('chalk');

class ForumTUI {
    constructor() {
        this.baseURL = 'http://localhost:8000/api';
        this.token = null;
        this.currentBoard = null;
        this.currentThread = null;
        
        this.screen = blessed.screen({
            smartCSR: true,
            title: 'Forum TUI Client'
        });

        this.setupUI();
        this.setupKeyBindings();
    }

    setupUI() {
        // Main container
        this.mainBox = blessed.box({
            parent: this.screen,
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            border: {
                type: 'line'
            },
            style: {
                border: {
                    fg: 'cyan'
                }
            }
        });

        // Header
        this.header = blessed.box({
            parent: this.mainBox,
            top: 0,
            left: 0,
            width: '100%',
            height: 3,
            content: ' Forum TUI Client - Press "q" to quit, "h" for help',
            style: {
                fg: 'white',
                bg: 'blue'
            }
        });

        // Content area
        this.contentBox = blessed.box({
            parent: this.mainBox,
            top: 3,
            left: 0,
            width: '100%',
            height: '100%-6',
            scrollable: true,
            alwaysScroll: true,
            mouse: true,
            keys: true,
            style: {
                fg: 'white'
            }
        });

        // Status bar
        this.statusBar = blessed.box({
            parent: this.mainBox,
            bottom: 0,
            left: 0,
            width: '100%',
            height: 3,
            content: ' Status: Not logged in',
            style: {
                fg: 'black',
                bg: 'white'
            }
        });
    }

    setupKeyBindings() {
        this.screen.key(['escape', 'q', 'C-c'], () => {
            process.exit(0);
        });

        this.screen.key(['h'], () => {
            this.showHelp();
        });

        this.screen.key(['l'], () => {
            if (!this.token) {
                this.login();
            }
        });

        this.screen.key(['b'], () => {
            if (this.token) {
                this.showBoards();
            }
        });

        this.screen.key(['t'], () => {
            if (this.token && this.currentBoard) {
                this.showThreads();
            }
        });

        this.screen.key(['n'], () => {
            if (this.token && this.currentBoard) {
                this.createThread();
            }
        });

        this.screen.key(['r'], () => {
            if (this.token && this.currentThread) {
                this.createReply();
            }
        });
    }

    async login() {
        this.screen.destroy();
        
        try {
            const answers = await inquirer.prompt([
                {
                    type: 'input',
                    name: 'publicKey',
                    message: 'Enter your public key:'
                }
            ]);

            console.log(chalk.blue('Getting challenge...'));
            const challengeResponse = await axios.post(`${this.baseURL}/auth/challenge`, {
                public_key: answers.publicKey
            });

            const signature = await inquirer.prompt([
                {
                    type: 'input',
                    name: 'signature',
                    message: `Sign this challenge: ${challengeResponse.data.challenge}`
                }
            ]);

            console.log(chalk.blue('Logging in...'));
            const loginResponse = await axios.post(`${this.baseURL}/auth/login`, {
                signature: signature.signature,
                challenge: challengeResponse.data.challenge,
                user_id: challengeResponse.data.user_id
            });

            this.token = loginResponse.data.token;
            this.user = loginResponse.data.user;

            console.log(chalk.green('Login successful!'));
            console.log(chalk.yellow('Press any key to continue...'));
            
            process.stdin.setRawMode(true);
            process.stdin.resume();
            process.stdin.once('data', () => {
                this.restart();
            });

        } catch (error) {
            console.log(chalk.red('Login failed:', error.response?.data?.error || error.message));
            console.log(chalk.yellow('Press any key to continue...'));
            
            process.stdin.setRawMode(true);
            process.stdin.resume();
            process.stdin.once('data', () => {
                this.restart();
            });
        }
    }

    restart() {
        process.stdin.setRawMode(false);
        process.stdin.pause();
        
        this.screen = blessed.screen({
            smartCSR: true,
            title: 'Forum TUI Client'
        });
        
        this.setupUI();
        this.setupKeyBindings();
        this.updateStatus();
        this.screen.render();
    }

    async showBoards() {
        try {
            const response = await axios.get(`${this.baseURL}/boards`, {
                headers: { Authorization: `Bearer ${this.token}` }
            });

            let content = '\n  📋 BOARDS\n\n';
            response.data.boards.forEach((board, index) => {
                content += `  ${index + 1}. /${board.code}/ - ${board.name}\n`;
                content += `     ${board.description}\n`;
                content += `     Threads: ${board.threads_count}\n\n`;
            });

            content += '\n  💡 Enter board code to select (e.g., type the code and press Enter)';
            
            this.contentBox.setContent(content);
            this.screen.render();

        } catch (error) {
            this.showError('Failed to load boards: ' + (error.response?.data?.error || error.message));
        }
    }

    async showThreads() {
        if (!this.currentBoard) return;

        try {
            const response = await axios.get(`${this.baseURL}/boards/${this.currentBoard}/threads`, {
                headers: { Authorization: `Bearer ${this.token}` }
            });

            let content = `\n  📝 THREADS IN /${this.currentBoard}/\n\n`;
            response.data.threads.forEach((thread, index) => {
                content += `  ${index + 1}. ${thread.title}\n`;
                content += `     By: ${thread.author_name} | Posts: ${thread.posts_count}\n`;
                content += `     ${thread.created_at}\n\n`;
            });

            content += '\n  💡 Press "n" to create new thread';
            
            this.contentBox.setContent(content);
            this.screen.render();

        } catch (error) {
            this.showError('Failed to load threads: ' + (error.response?.data?.error || error.message));
        }
    }

    async createThread() {
        this.screen.destroy();
        
        try {
            const answers = await inquirer.prompt([
                {
                    type: 'input',
                    name: 'title',
                    message: 'Thread title:'
                },
                {
                    type: 'input',
                    name: 'content',
                    message: 'Thread content:'
                }
            ]);

            console.log(chalk.blue('Creating thread...'));
            const response = await axios.post(`${this.baseURL}/boards/${this.currentBoard}/threads`, answers, {
                headers: { Authorization: `Bearer ${this.token}` }
            });

            console.log(chalk.green('Thread created successfully!'));
            console.log(chalk.yellow('Press any key to continue...'));
            
            process.stdin.setRawMode(true);
            process.stdin.resume();
            process.stdin.once('data', () => {
                this.restart();
            });

        } catch (error) {
            console.log(chalk.red('Failed to create thread:', error.response?.data?.error || error.message));
            console.log(chalk.yellow('Press any key to continue...'));
            
            process.stdin.setRawMode(true);
            process.stdin.resume();
            process.stdin.once('data', () => {
                this.restart();
            });
        }
    }

    showHelp() {
        const helpText = `
  🔧 FORUM TUI CLIENT - HELP

  Keyboard Shortcuts:
  • l - Login with public key
  • b - Show boards (after login)
  • t - Show threads in current board
  • n - Create new thread (in a board)
  • r - Create reply (in a thread)
  • h - Show this help
  • q - Quit

  Navigation:
  1. First login with 'l'
  2. View boards with 'b'
  3. Select a board by typing its code
  4. View threads with 't'
  5. Create content with 'n' or 'r'

  Status: ${this.token ? 'Logged in' : 'Not logged in'}
  Current Board: ${this.currentBoard || 'None'}
        `;

        this.contentBox.setContent(helpText);
        this.screen.render();
    }

    showError(message) {
        this.contentBox.setContent(`\n  ❌ ERROR: ${message}\n\n  Press 'h' for help`);
        this.screen.render();
    }

    updateStatus() {
        const status = this.token 
            ? `Logged in as ${this.user?.name} | Board: ${this.currentBoard || 'None'}`
            : 'Not logged in - Press "l" to login';
        
        this.statusBar.setContent(` Status: ${status}`);
    }

    start() {
        this.showHelp();
        this.updateStatus();
        this.screen.render();
    }
}

// Start the TUI application
const app = new ForumTUI();
app.start();
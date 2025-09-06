class h{constructor(){this.isActive=!1,this.currentTarget=null,this.currentTargetType=null,this.currentTargetId=null,this.startTime=0,this.nonce=0,this.hashCount=0,this.pattern="21",this.ui=null,this.miningInterval=null,this.hashRateInterval=null,this.submitQueue=[],this.intensity=1,this.sessionProofs=0,this.isMinimized=localStorage.getItem("haichan-miner-minimized")==="true",this.buzzingEnabled=!0,this.init()}init(){this.createUI(),this.attachEventListeners(),this.switchTarget("global","haichan","Global Network"),this.startGlobalMining()}createUI(){this.ui=document.createElement("div"),this.ui.id="haichan-miner",this.ui.innerHTML=`
            <div class="miner-header">
                <span class="miner-title">⛏️ Haichan Miner</span>
                <div class="miner-controls">
                    <button class="miner-minimize" onclick="window.haichanMiner.toggleMinimize()">_</button>
                    <button class="miner-toggle" onclick="window.haichanMiner.toggle()">⏸️</button>
                </div>
            </div>
            <div class="miner-content">
                <div class="mining-mode-controls">
                    <button id="float-mode-idle" class="mining-mode-btn active" title="~100 H/s - Easy pattern">🟢 IDLE<br><span style="font-size: 7pt;">~100 H/s</span></button>
                    <button id="float-mode-active" class="mining-mode-btn" title="~1000 H/s - Normal">🟡 ACTIVE<br><span style="font-size: 7pt;">~1K H/s</span></button>
                    <button id="float-mode-hyperactive" class="mining-mode-btn" title="~3000 H/s - High CPU">🔴 HYPER<br><span style="font-size: 7pt;">~3K H/s</span></button>
                </div>
                <div class="miner-target">
                    <span class="target-label">Mining:</span>
                    <span class="target-value" id="mining-target">Idle</span>
                </div>
                <div class="miner-stats">
                    <div class="stat">
                        <span class="stat-label">Hash Rate:</span>
                        <span class="stat-value" id="hash-rate">0 H/s</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Proofs:</span>
                        <span class="stat-value" id="proof-count">0</span>
                    </div>
                </div>
                <div class="miner-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                </div>
                <div class="miner-log" id="miner-log">
                    <div class="log-entry">🚀 Miner initialized</div>
                </div>
            </div>
        `;const t=document.createElement("style");t.textContent=`
            #haichan-miner {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 300px;
                background: linear-gradient(135deg, #F5F5DC, #E8E8D0);
                border: 2px solid #708B75;
                border-radius: 8px;
                color: #444B6E;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                box-shadow: 0 8px 24px rgba(112, 139, 117, 0.2);
                z-index: 10000;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }
            .miner-header {
                background: linear-gradient(135deg, #708B75, #5A7B5F);
                padding: 10px 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 6px 6px 0 0;
                border-bottom: 1px solid #4A6B4F;
            }
            .miner-title {
                font-weight: bold;
                color: #F5F5DC;
                text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            }
            .miner-controls {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .miner-minimize, .miner-toggle {
                background: rgba(245, 245, 220, 0.1);
                border: 1px solid rgba(245, 245, 220, 0.3);
                border-radius: 4px;
                padding: 4px 8px;
                color: #F5F5DC;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.2s ease;
            }
            .miner-minimize:hover, .miner-toggle:hover {
                background: rgba(245, 245, 220, 0.2);
                transform: scale(1.05);
            }
            
            /* Strobing buzz animation for active mining */
            @keyframes buzz-strobe {
                0%, 100% { 
                    transform: translateX(0); 
                    border-color: #708B75;
                    box-shadow: 0 8px 24px rgba(112, 139, 117, 0.2);
                }
                10% { 
                    transform: translateX(-1px) translateY(-1px); 
                    border-color: #ff6b35;
                    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.4);
                }
                20% { 
                    transform: translateX(1px) translateY(1px); 
                    border-color: #2ecc71;
                    box-shadow: 0 8px 24px rgba(46, 204, 113, 0.4);
                }
                30% { 
                    transform: translateX(-1px) translateY(1px); 
                    border-color: #9b59b6;
                    box-shadow: 0 8px 24px rgba(155, 89, 182, 0.4);
                }
                40% { 
                    transform: translateX(1px) translateY(-1px); 
                    border-color: #3498db;
                    box-shadow: 0 8px 24px rgba(52, 152, 219, 0.4);
                }
                50% { 
                    transform: translateX(0) translateY(-2px); 
                    border-color: #f1c40f;
                    box-shadow: 0 8px 24px rgba(241, 196, 15, 0.4);
                }
                60% { 
                    transform: translateX(-2px) translateY(0); 
                    border-color: #e74c3c;
                    box-shadow: 0 8px 24px rgba(231, 76, 60, 0.4);
                }
                70% { 
                    transform: translateX(2px) translateY(2px); 
                    border-color: #1abc9c;
                    box-shadow: 0 8px 24px rgba(26, 188, 156, 0.4);
                }
                80% { 
                    transform: translateX(0) translateY(1px); 
                    border-color: #34495e;
                    box-shadow: 0 8px 24px rgba(52, 73, 94, 0.4);
                }
                90% { 
                    transform: translateX(1px) translateY(0); 
                    border-color: #95a5a6;
                    box-shadow: 0 8px 24px rgba(149, 165, 166, 0.4);
                }
            }
            
            .miner-buzzing {
                animation: buzz-strobe 0.8s infinite ease-in-out;
            }
            .miner-minimized .miner-content {
                display: none;
            }
            .miner-minimized {
                width: auto;
            }
            .miner-content {
                padding: 12px;
            }
            .mining-mode-controls {
                display: flex;
                gap: 10px;
                margin-bottom: 15px;
                justify-content: center;
                padding-bottom: 12px;
                border-bottom: 1px solid #708B75;
            }
            .mining-mode-btn {
                border: none;
                padding: 8px 16px;
                font-size: 9pt;
                font-weight: bold;
                cursor: pointer;
                border-radius: 3px;
                opacity: 0.7;
                transition: all 0.2s ease;
                line-height: 1.2;
            }
            #float-mode-idle {
                background: #28a745;
                color: white;
            }
            #float-mode-active {
                background: #ffc107;
                color: #444;
            }
            #float-mode-hyperactive {
                background: #dc3545;
                color: white;
            }
            .mining-mode-btn:hover {
                opacity: 0.9;
            }
            .mining-mode-btn.active {
                opacity: 1;
                transform: scale(1.05);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            .miner-target {
                display: flex;
                justify-content: space-between;
                margin-bottom: 12px;
                padding: 8px 12px;
                background: linear-gradient(135deg, rgba(112, 139, 117, 0.1), rgba(112, 139, 117, 0.05));
                border: 1px solid rgba(112, 139, 117, 0.3);
                border-radius: 6px;
            }
            .target-label {
                color: #444B6E;
                font-weight: 500;
            }
            .target-value {
                font-weight: bold;
                color: #708B75;
            }
            .miner-stats {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }
            .stat {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .stat-label {
                font-size: 10px;
                opacity: 0.7;
            }
            .stat-value {
                font-weight: bold;
                color: #708B75;
                text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            }
            .miner-progress {
                margin-bottom: 10px;
            }
            .progress-bar {
                width: 100%;
                height: 8px;
                background: #444;
                border-radius: 4px;
                overflow: hidden;
            }
            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #708B75, #5A7B5F);
                width: 0%;
                transition: width 0.3s ease;
                border-radius: 4px;
            }
            .miner-log {
                max-height: 80px;
                overflow-y: auto;
                background: rgba(112, 139, 117, 0.05);
                border: 1px solid rgba(112, 139, 117, 0.2);
                border-radius: 6px;
                padding: 8px;
            }
            .log-entry {
                font-size: 10px;
                margin: 2px 0;
                opacity: 0.9;
                color: #444B6E;
            }
            .log-entry.success {
                color: #28a745;
                font-weight: 500;
            }
            .log-entry.error {
                color: #dc3545;
                font-weight: 500;
            }
        `,document.head.appendChild(t),document.body.appendChild(this.ui),this.isMinimized&&this.ui.classList.add("miner-minimized")}attachMiningModeControls(){this.updateMiningModeDisplay("idle");const e=document.getElementById("mode-idle"),n=document.getElementById("mode-active"),i=document.getElementById("mode-hyperactive"),s=document.getElementById("float-mode-idle"),a=document.getElementById("float-mode-active"),r=document.getElementById("float-mode-hyperactive");e&&e.addEventListener("click",()=>this.setMiningMode("idle")),n&&n.addEventListener("click",()=>this.setMiningMode("active")),i&&i.addEventListener("click",()=>this.setMiningMode("hyperactive")),s&&s.addEventListener("click",()=>this.setMiningMode("idle")),a&&a.addEventListener("click",()=>this.setMiningMode("active")),r&&r.addEventListener("click",()=>this.setMiningMode("hyperactive")),setInterval(()=>{this.updateDashboard()},1e3)}setMiningMode(t){switch(this.updateMiningModeDisplay(t),t){case"idle":this.setIntensity(.1),this.pattern="21";break;case"active":this.setIntensity(1),this.pattern="21e8";break;case"hyperactive":this.setIntensity(3),this.pattern="21e8";break}const n={idle:"Global Network (IDLE)",active:"Global Network (ACTIVE)",hyperactive:"Global Network (HYPER)"}[t]||"Global Network",i=document.getElementById("mining-target");i&&(i.textContent=n),this.currentTarget=n,this.log(`🎯 Mining mode set to ${t.toUpperCase()} - Pattern: ${this.pattern}`)}updateMiningModeDisplay(t){document.querySelectorAll(".mining-mode-btn").forEach(i=>{i.classList.remove("active"),i.style.opacity="0.5",i.style.transform="scale(1)"});const e=[document.getElementById("mode-"+t)],n=document.getElementById("float-mode-"+t);[...e,n].forEach(i=>{i&&(i.classList.add("active"),i.style.opacity="1",i.style.transform="scale(1.1)")})}updateDashboard(){if(this.isActive){const t=(Date.now()-this.startTime)/1e3,e=Math.round(this.hashCount/Math.max(t,1)),n=document.getElementById("mining-target"),i=document.getElementById("current-hashrate"),s=document.getElementById("session-stats");n&&(n.textContent=this.currentTarget||"Global Network"),i&&(i.textContent=e+" H/s"),s&&(s.textContent=this.hashCount+" hashes | "+(this.sessionProofs||0)+" proofs")}}attachEventListeners(){this.attachMiningModeControls(),document.addEventListener("mouseover",t=>{const e=t.target.closest("[data-mine-type]");if(e){const a=e.dataset.mineType,r=e.dataset.mineTarget;if(a==="reply"){const o=e.dataset.postId||r.replace("reply-","");this.switchTarget("reply",o,`Reply #${o}`);return}else if(a==="thread"){const o=e.dataset.threadId||r.replace("thread-",""),d=e.dataset.threadTitle||`Thread #${o}`;this.switchTarget("thread",o,d);return}}const n=t.target.closest("[data-thread-id]");if(n){const a=n.dataset.threadId,r=n.dataset.threadTitle||`Thread #${a}`;this.switchTarget("thread",a,r)}const i=t.target.closest("[data-user-id]");if(i&&!n&&!e){const a=i.dataset.userId,r=i.dataset.userName||`User #${a}`;this.switchTarget("user",a,r)}const s=t.target.closest("[data-board-code]")||t.target.closest("[data-board-name]");if(s&&!n&&!i&&!e){const a=s.dataset.boardCode||s.dataset.boardName,r=s.dataset.boardName||s.dataset.boardCode||`/${a}/`;this.switchTarget("board",a,r)}}),document.addEventListener("mouseover",t=>{!t.target.closest("[data-thread-id]")&&!t.target.closest("[data-user-id]")&&!t.target.closest("[data-board-code]")&&!t.target.closest("[data-board-name]")&&!t.target.closest("[data-mine-type]")&&this.switchTarget("global","haichan","Global Network")})}switchTarget(t,e,n){if(this.currentTargetType===t&&this.currentTargetId===e)return;this.currentTargetType=t,this.currentTargetId=e,this.currentTarget=n;const i=document.getElementById("mining-target");i&&(i.textContent=n),this.log(`🎯 Switched to mining: ${n}`),this.nonce=Math.floor(Math.random()*1e6),this.resetProgress()}startGlobalMining(){if(this.isActive)return;this.isActive=!0,this.startTime=Date.now(),this.hashCount=0;const e=Math.max(1,10/this.intensity);this.miningInterval=setInterval(()=>{this.mineStep()},e),this.hashRateInterval=setInterval(()=>{this.updateStats()},1e3),this.buzzingEnabled&&this.ui&&this.ui.classList.add("miner-buzzing"),this.log("🚀 Mining started")}mineStep(){if(!this.isActive||!this.currentTargetType)return;const t=this.generateMiningData(),e=`${t}:${this.nonce}`;this.sha256(e).then(n=>{this.hashCount++;const i=this.pattern.toLowerCase();n.toLowerCase().startsWith(i)&&this.foundProof(n,this.nonce,t),this.nonce++})}generateMiningData(){var e;const t=Date.now();if(this.currentTargetType==="reply"){const n=((e=document.querySelector("[data-thread-id]"))==null?void 0:e.dataset.threadId)||"unknown";return`${this.currentTargetType}:${this.currentTargetId}:thread-${n}:${t}`}return`${this.currentTargetType}:${this.currentTargetId}:${t}`}async sha256(t){const e=new TextEncoder().encode(t),n=await crypto.subtle.digest("SHA-256",e);return Array.from(new Uint8Array(n)).map(s=>s.toString(16).padStart(2,"0")).join("")}foundProof(t,e,n){var r;const i=parseInt(((r=document.getElementById("proof-count"))==null?void 0:r.textContent)||0)+1;document.getElementById("proof-count")&&(document.getElementById("proof-count").textContent=i),this.sessionProofs=(this.sessionProofs||0)+1;const s=this.getProofDifficulty(t),a=this.getProofRarity(t);this.log(`${s.emoji} PROOF FOUND! ${t.substring(0,16)}... (${this.currentTarget}) [${a.name}]`,"success"),this.submitProof(t,e,n),this.celebrateProof(a,t),this.nonce=Math.floor(Math.random()*1e6)}getProofDifficulty(t){return t.startsWith("000021e8")?{emoji:"🌟",pattern:"000021e8"}:t.startsWith("21e8000")?{emoji:"💎",pattern:"21e8000"}:t.startsWith("21e800")?{emoji:"🔥",pattern:"21e800"}:t.startsWith("21e80")?{emoji:"✨",pattern:"21e80"}:t.startsWith("21e8")?{emoji:"🎯",pattern:"21e8"}:t.startsWith("21")?{emoji:"🟢",pattern:"21"}:{emoji:"🟢",pattern:"21"}}getProofRarity(t){return t.startsWith("000021e8")?{name:"LEGENDARY",color:"#ff6b35",intensity:"epic",sound:"legendary"}:t.startsWith("21e8000")?{name:"RARE",color:"#9b59b6",intensity:"strong",sound:"rare"}:t.startsWith("21e800")?{name:"UNCOMMON",color:"#3498db",intensity:"medium",sound:"uncommon"}:t.startsWith("21e80")?{name:"COMMON+",color:"#2ecc71",intensity:"light",sound:"common"}:t.startsWith("21")?{name:"IDLE",color:"#95a5a6",intensity:"minimal",sound:"basic"}:{name:"COMMON",color:"#95a5a6",intensity:"minimal",sound:"basic"}}celebrateProof(t,e){switch(t.intensity){case"epic":this.epicCelebration(t,e);break;case"strong":this.strongCelebration(t,e);break;case"medium":this.mediumCelebration(t,e);break;case"light":this.lightCelebration(t,e);break;default:this.basicCelebration(t,e)}}epicCelebration(t,e){this.screenFlash(t.color),this.shakeUI("epic"),this.createSparks("epic",t.color),this.screenText("🌟 LEGENDARY PROOF! 🌟",t.color),this.playSound("legendary"),this.epicProgressAnimation()}strongCelebration(t,e){this.shakeUI("strong"),this.createSparks("strong",t.color),this.screenText("💎 RARE PROOF! 💎",t.color),this.playSound("rare"),this.strongProgressAnimation()}mediumCelebration(t,e){this.shakeUI("medium"),this.createSparks("medium",t.color),this.screenText("🔥 UNCOMMON! 🔥",t.color),this.playSound("uncommon"),this.mediumProgressAnimation()}lightCelebration(t,e){this.pulseUI(),this.createSparks("light",t.color),this.playSound("common"),this.lightProgressAnimation()}basicCelebration(t,e){this.flashProgress(),this.playSound("basic")}screenFlash(t){const e=document.createElement("div");e.style.cssText=`
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: ${t};
            opacity: 0.3;
            z-index: 999999;
            pointer-events: none;
            animation: flashAnimation 0.5s ease-out;
        `;const n=document.createElement("style");n.textContent=`
            @keyframes flashAnimation {
                0% { opacity: 0.5; }
                50% { opacity: 0.8; }
                100% { opacity: 0; }
            }
        `,document.head.appendChild(n),document.body.appendChild(e),setTimeout(()=>{document.body.removeChild(e),document.head.removeChild(n)},500)}shakeUI(t){const e=`shake-${t}`,n=document.createElement("style");let i="";switch(t){case"epic":i=`
                    @keyframes shake-epic {
                        0%, 100% { transform: translate(0, 0) rotate(0deg); }
                        10% { transform: translate(-10px, -5px) rotate(-2deg); }
                        20% { transform: translate(10px, 5px) rotate(2deg); }
                        30% { transform: translate(-8px, 3px) rotate(-1deg); }
                        40% { transform: translate(8px, -3px) rotate(1deg); }
                        50% { transform: translate(-6px, 2px) rotate(-1deg); }
                        60% { transform: translate(6px, -2px) rotate(1deg); }
                        70% { transform: translate(-4px, 1px) rotate(-0.5deg); }
                        80% { transform: translate(4px, -1px) rotate(0.5deg); }
                        90% { transform: translate(-2px, 0px) rotate(0deg); }
                    }
                    .shake-epic { animation: shake-epic 1s ease-in-out; }
                `;break;case"strong":i=`
                    @keyframes shake-strong {
                        0%, 100% { transform: translate(0, 0) rotate(0deg); }
                        25% { transform: translate(-5px, -2px) rotate(-1deg); }
                        50% { transform: translate(5px, 2px) rotate(1deg); }
                        75% { transform: translate(-3px, 1px) rotate(-0.5deg); }
                    }
                    .shake-strong { animation: shake-strong 0.6s ease-in-out; }
                `;break;case"medium":i=`
                    @keyframes shake-medium {
                        0%, 100% { transform: translate(0, 0); }
                        25% { transform: translate(-3px, -1px); }
                        50% { transform: translate(3px, 1px); }
                        75% { transform: translate(-2px, 0px); }
                    }
                    .shake-medium { animation: shake-medium 0.4s ease-in-out; }
                `;break}n.textContent=i,document.head.appendChild(n),this.ui.classList.add(e),setTimeout(()=>{this.ui.classList.remove(e),document.head.removeChild(n)},1e3)}createSparks(t,e){const n=t==="epic"?15:t==="strong"?10:t==="medium"?6:3,i=this.ui.getBoundingClientRect();for(let s=0;s<n;s++)this.createSingleSpark(i,e,t)}createSingleSpark(t,e,n){const i=document.createElement("div"),s=n==="epic"?8:n==="strong"?6:4,a=n==="epic"?2e3:1500;i.style.cssText=`
            position: fixed;
            width: ${s}px;
            height: ${s}px;
            background: ${e};
            border-radius: 50%;
            z-index: 999999;
            pointer-events: none;
            box-shadow: 0 0 ${s*2}px ${e};
        `;const r=t.left+t.width/2+(Math.random()-.5)*50,o=t.top+t.height/2+(Math.random()-.5)*50,d=r+(Math.random()-.5)*200,l=o+(Math.random()-.5)*200;i.style.left=r+"px",i.style.top=o+"px",document.body.appendChild(i),i.animate([{left:r+"px",top:o+"px",opacity:1,transform:"scale(1)"},{left:d+"px",top:l+"px",opacity:0,transform:"scale(0)"}],{duration:a,easing:"cubic-bezier(0.25, 0.46, 0.45, 0.94)"}).onfinish=()=>{document.body.removeChild(i)}}screenText(t,e){const n=document.createElement("div");n.textContent=t,n.style.cssText=`
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Courier New', monospace;
            font-size: 2rem;
            font-weight: bold;
            color: ${e};
            text-shadow: 0 0 20px ${e}, 0 0 40px ${e};
            z-index: 999999;
            pointer-events: none;
            animation: textPulse 2s ease-out;
        `;const i=document.createElement("style");i.textContent=`
            @keyframes textPulse {
                0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
                10% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
                20% { transform: translate(-50%, -50%) scale(1); }
                90% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            }
        `,document.head.appendChild(i),document.body.appendChild(n),setTimeout(()=>{document.body.removeChild(n),document.head.removeChild(i)},2e3)}epicProgressAnimation(){const t=document.getElementById("progress-fill");t.style.background="linear-gradient(90deg, #ff6b35, #ffd700, #ff6b35)",t.style.width="100%",t.style.boxShadow="0 0 20px #ff6b35",setTimeout(()=>{t.style.background="linear-gradient(90deg, #00ff88, #ffd700)",t.style.width="0%",t.style.boxShadow="none"},1e3)}strongProgressAnimation(){const t=document.getElementById("progress-fill");t.style.background="linear-gradient(90deg, #9b59b6, #ffd700)",t.style.width="100%",t.style.boxShadow="0 0 15px #9b59b6",setTimeout(()=>{t.style.background="linear-gradient(90deg, #00ff88, #ffd700)",t.style.width="0%",t.style.boxShadow="none"},800)}mediumProgressAnimation(){const t=document.getElementById("progress-fill");t.style.background="linear-gradient(90deg, #3498db, #ffd700)",t.style.width="100%",setTimeout(()=>{t.style.background="linear-gradient(90deg, #00ff88, #ffd700)",t.style.width="0%"},600)}lightProgressAnimation(){const t=document.getElementById("progress-fill");t.style.background="linear-gradient(90deg, #2ecc71, #ffd700)",t.style.width="100%",setTimeout(()=>{t.style.background="linear-gradient(90deg, #00ff88, #ffd700)",t.style.width="0%"},400)}pulseUI(){this.ui.style.animation="pulse 0.5s ease-in-out",setTimeout(()=>{this.ui.style.animation=""},500)}playSound(t){if(typeof AudioContext<"u"||typeof webkitAudioContext<"u"){const e=AudioContext||webkitAudioContext,n=new e;switch(t){case"legendary":this.playChime(n,[523,659,784],500);break;case"rare":this.playChime(n,[440,554],300);break;case"uncommon":this.playChime(n,[440],200);break;case"common":this.playTone(n,550,100);break;default:this.playTone(n,440,50)}}}playTone(t,e,n){const i=t.createOscillator(),s=t.createGain();i.connect(s),s.connect(t.destination),i.frequency.setValueAtTime(e,t.currentTime),i.type="sine",s.gain.setValueAtTime(.1,t.currentTime),s.gain.exponentialRampToValueAtTime(.01,t.currentTime+n/1e3),i.start(t.currentTime),i.stop(t.currentTime+n/1e3)}playChime(t,e,n){e.forEach((i,s)=>{setTimeout(()=>{this.playTone(t,i,n/2)},s*100)})}async submitProof(t,e,n){var i;try{const a=this.getProofDifficulty(t).pattern;if(!["000021e8","21e8000","21e800","21e80","21e8","21"].some(c=>t.toLowerCase().startsWith(c.toLowerCase()))){this.log("❌ Invalid hash detected - no valid pattern found","error");return}const l=await(await fetch("/api/submit-proof",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":((i=document.querySelector('meta[name="csrf-token"]'))==null?void 0:i.content)||""},body:JSON.stringify({hash:t,nonce:e,data:n,pattern:a,target_type:this.currentTargetType,target_id:this.currentTargetId})})).json();l.success?this.log("✅ Proof accepted! Added to thread ranking","success"):this.log(`❌ Proof rejected: ${l.message}`,"error")}catch(s){this.log(`❌ Network error: ${s.message}`,"error")}}updateStats(){if(!this.isActive)return;const t=(Date.now()-this.startTime)/1e3,e=Math.floor(this.hashCount/t);document.getElementById("hash-rate").textContent=`${e.toLocaleString()} H/s`;const n=this.hashCount%1e4/100;document.getElementById("progress-fill").style.width=`${n}%`}flashProgress(){const t=document.getElementById("progress-fill");t.style.background="linear-gradient(90deg, #ffd700, #00ff88)",t.style.width="100%",setTimeout(()=>{t.style.background="linear-gradient(90deg, #00ff88, #ffd700)",t.style.width="0%"},500)}resetProgress(){document.getElementById("progress-fill").style.width="0%"}log(t,e="info"){const n=document.getElementById("miner-log");if(!n)return;const i=document.createElement("div");for(i.className=`log-entry ${e}`,i.textContent=`[${new Date().toLocaleTimeString()}] ${t}`,n.appendChild(i),n.scrollTop=n.scrollHeight;n.children.length>20;)n.removeChild(n.firstChild)}toggle(){this.isActive?this.stop():this.startGlobalMining()}toggleMinimize(){this.isMinimized=!this.isMinimized,localStorage.setItem("haichan-miner-minimized",this.isMinimized.toString()),this.isMinimized?this.ui.classList.add("miner-minimized"):this.ui.classList.remove("miner-minimized")}setIntensity(t){this.intensity=Math.max(.1,Math.min(5,t)),this.isActive&&this.restartMiningWithNewIntensity(),this.log(`⚡ Mining intensity set to ${t}x`)}restartMiningWithNewIntensity(){this.miningInterval&&clearInterval(this.miningInterval);const e=Math.max(1,10/this.intensity);this.miningInterval=setInterval(()=>{this.mineStep()},e)}stop(){this.isActive=!1,this.miningInterval&&(clearInterval(this.miningInterval),this.miningInterval=null),this.hashRateInterval&&(clearInterval(this.hashRateInterval),this.hashRateInterval=null),this.ui&&this.ui.classList.remove("miner-buzzing"),document.getElementById("hash-rate").textContent="0 H/s",document.querySelector(".miner-toggle").textContent="▶️",this.log("⏸️ Mining stopped")}}document.addEventListener("DOMContentLoaded",()=>{window.haichanMiner=new h});

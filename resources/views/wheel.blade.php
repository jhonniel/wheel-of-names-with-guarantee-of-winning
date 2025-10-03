<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Wheel of Names</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            @if($activeBackground)
                background: url('{{ asset('storage/' . $activeBackground->image_path) }}') center/cover no-repeat;
            @else
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            @endif
            margin: 0;
        }
        #wheel {
            transform: rotate(-90deg);
            transition: transform {{ $spinDuration }}s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
        }
        button {
            padding: 12px 24px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            background: #4CAF50;
            color: white;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        button:hover { background: #45a049; transform: translateY(-2px); }
        button:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .winner-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; place-items: center; z-index: 1000; }
        .winner-content { background: white; padding: 40px; border-radius: 20px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .winner-name { font-size: 3rem; font-weight: bold; margin-bottom: 20px; }
        .confetti { position: fixed; width: 10px; height: 10px; background: #f00; animation: confetti-fall 3s linear infinite; }
        @keyframes confetti-fall { 0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; } 100% { transform: translateY(100vh) rotate(720deg); opacity: 0; } }
        .winner-arrow { position: absolute; width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-bottom: 12px solid #ff0000; z-index: 5; }
        .rolling-box {
            width: min(80vw, 640px);
            height: 60px;
            border: 3px solid #333;
            border-radius: 12px;
            display: grid;
            place-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            margin-bottom: 12px;
        }
        .rolling-text {
            font-weight: 700;
            font-size: 24px;
            white-space: nowrap;
            transition: transform 0.3s ease;
            color: #333;
        }
        .rolling-box.zoom { transform: scale(1.2); }
        .rolling-text.zoom { transform: scale(1.1); }
        .wheel-container {
            position: relative;
            display: inline-block;
            padding: 8px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .wheel-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 10;
        }
        .wheel-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }
    </style>
    @csrf
</head>
<body>
    @if (session('status'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid #c3e6cb;">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
    @endif
    @if($showTotalEntries)
        <!-- Total Entries Display -->
        <div id="totalEntriesDisplay" style="margin-bottom: 16px; text-align: center;">
            <div style="font-size: 18px; font-weight: bold; color: #333; background: rgba(255,255,255,0.9); padding: 12px 24px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                <span id="totalEntriesText">Total Entries: <span id="totalEntriesCount">0</span></span>
            </div>
        </div>
    @endif

    @if($displayMode === 'rolling' || $displayMode === 'both')
        <div class="rolling-box"><span id="rollerText" class="rolling-text"></span></div>
    @endif

    @if($displayMode === 'wheel' || $displayMode === 'both')
        <div class="wheel-container">
            <svg id="wheel" viewBox="-210 -210 420 420"></svg>
            @if($activeLogo)
                <div class="wheel-logo">
                    <img src="{{ asset('storage/' . $activeLogo->image_path) }}" alt="{{ $activeLogo->name }}" />
                </div>
            @endif
            <div id="winnerArrow" class="winner-arrow" style="display: none;"></div>
        </div>
    @endif
    <div>
        <button id="spinBtn">Spin</button>
        <span id="winnerLabel"></span>
    </div>


    <!-- Countdown Timer -->
    <div id="countdownTimer" style="display: none; margin-top: 16px; text-align: center;">
        <div style="font-size: 24px; font-weight: bold; color: #333; background: rgba(255,255,255,0.9); padding: 12px 24px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <span id="countdownText">Spinning...</span>
            <div style="font-size: 14px; color: #666; margin-top: 4px;">Time remaining</div>
        </div>
    </div>

    <div id="winnerModal" class="winner-modal">
        <div class="winner-content">
            <div class="winner-name" id="winnerName"></div>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button onclick="recordWinner()" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Record Winner & Remove from Participants</button>
                <button onclick="closeWinnerModal()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
<script>
const wheel = document.getElementById('wheel');
const spinBtn = document.getElementById('spinBtn');
const winnerLabel = document.getElementById('winnerLabel');
const rollerText = document.getElementById('rollerText');
const rollingBox = document.querySelector('.rolling-box');
const displayMode = '{{ $displayMode }}';

// Audio system for rolling effects
let audioContext = null;
let isAudioEnabled = {{ $audioEnabled ? 'true' : 'false' }};
let rollingAudioInterval = null;

let segments = [];
let currentRotation = 0;
let rollingTimer = null;
let rollingIndex = 0;
let currentWinner = null;
let countdownTimer = null;
let currentSpinDuration = {{ $spinDuration }};

// Initialize audio system
function initAudio() {
  try {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    console.log('Audio system initialized');
  } catch (error) {
    console.log('Audio not supported:', error);
    isAudioEnabled = false;
    audioContext = null;
  }
}

// Create realistic wheel rolling sound
function createRollingSound() {
  if (!audioContext || !isAudioEnabled) return null;

  try {
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    const filterNode = audioContext.createBiquadFilter();

    // Create a low-frequency rumble with some variation
    oscillator.type = 'sawtooth';
    const baseFreq = 60 + Math.random() * 40; // 60-100 Hz for deep rumble
    oscillator.frequency.setValueAtTime(baseFreq, audioContext.currentTime);

    // Add slight frequency modulation for realism
    oscillator.frequency.linearRampToValueAtTime(
      baseFreq + (Math.random() - 0.5) * 20,
      audioContext.currentTime + 0.1
    );

    // Low-pass filter to make it sound more like mechanical rolling
    filterNode.type = 'lowpass';
    filterNode.frequency.setValueAtTime(200, audioContext.currentTime);
    filterNode.Q.setValueAtTime(1, audioContext.currentTime);

    // Volume envelope - quick attack, short sustain
    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.15, audioContext.currentTime + 0.01);
    gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.05);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

    // Connect the audio chain
    oscillator.connect(filterNode);
    filterNode.connect(gainNode);
    gainNode.connect(audioContext.destination);

    return { oscillator, gainNode, filterNode };
  } catch (error) {
    console.log('Error creating rolling sound:', error);
    return null;
  }
}

// Create wheel tick sound (for each name change)
function createTickSound() {
  if (!audioContext || !isAudioEnabled) return null;

  try {
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    // Create a sharp click/tick sound
    oscillator.type = 'square';
    const freq = 800 + Math.random() * 400; // 800-1200 Hz
    oscillator.frequency.setValueAtTime(freq, audioContext.currentTime);

    // Very short, sharp sound
    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.02);

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    return { oscillator, gainNode };
  } catch (error) {
    console.log('Error creating tick sound:', error);
    return null;
  }
}

// Start rolling audio effect
function startRollingAudio(interval) {
  if (!isAudioEnabled || !audioContext) return;

  try {
    if (audioContext.state === 'suspended') {
      audioContext.resume();
    }

    // Clear any existing interval
    if (rollingAudioInterval) {
      clearInterval(rollingAudioInterval);
    }

    // Play rolling sound at the specified interval
    rollingAudioInterval = setInterval(() => {
      const rollingSound = createRollingSound();
      if (rollingSound) {
        rollingSound.oscillator.start();
        rollingSound.oscillator.stop(audioContext.currentTime + 0.15);
      }
    }, interval);
  } catch (error) {
    console.log('Error starting rolling audio:', error);
  }
}

// Stop rolling audio effect
function stopRollingAudio() {
  if (rollingAudioInterval) {
    clearInterval(rollingAudioInterval);
    rollingAudioInterval = null;
  }
}

// Play tick sound for name changes
function playTickSound() {
  if (!isAudioEnabled || !audioContext) return;

  try {
    if (audioContext.state === 'suspended') {
      audioContext.resume();
    }

    const tickSound = createTickSound();
    if (tickSound) {
      tickSound.oscillator.start();
      tickSound.oscillator.stop(audioContext.currentTime + 0.02);
    }
  } catch (error) {
    console.log('Error playing tick sound:', error);
  }
}


async function fetchSegments() {
  try {
    const res = await fetch('/api/wheel');
    const data = await res.json();
    segments = data.segments || [];
    console.log('Fetched segments:', segments.length);
    updateWheelSize();
    renderWheel();
    updateTotalEntriesDisplay();
    // Initialize roller with random name if available
    if (segments.length && rollerText) {
      const randomIndex = Math.floor(Math.random() * segments.length);
      rollerText.textContent = segments[randomIndex].label;
    }
  } catch (error) {
    console.error('Error fetching segments:', error);
  }
}


function updateWheelSize() {
  if (!wheel) return; // Exit if wheel doesn't exist (rolling only mode)

  const n = Math.max(1, segments.length);
  const minSize = 400;
  const maxSize = Math.min(800, window.innerWidth * 0.8, window.innerHeight * 0.7);

  // Calculate size based on number of segments - more conservative sizing
  let size;
  if (n <= 8) {
    size = minSize;
  } else if (n <= 20) {
    size = minSize + (n - 8) * 8;
  } else if (n <= 50) {
    size = minSize + 12 * 8 + (n - 20) * 4;
  } else if (n <= 100) {
    size = minSize + 12 * 8 + 30 * 4 + (n - 50) * 2;
  } else if (n <= 200) {
    size = minSize + 12 * 8 + 30 * 4 + 50 * 2 + (n - 100) * 1;
  } else {
    size = Math.min(maxSize, minSize + 12 * 8 + 30 * 4 + 50 * 2 + 100 * 1 + (n - 200) * 0.5);
  }

  size = Math.max(minSize, Math.min(maxSize, size));
  console.log('Wheel size calculated:', size, 'for', n, 'segments');

  wheel.style.width = size + 'px';
  wheel.style.height = size + 'px';

  // Update viewBox to match size
  const halfSize = size / 2;
  wheel.setAttribute('viewBox', `-${halfSize} -${halfSize} ${size} ${size}`);
}

function updateTotalEntriesDisplay() {
  const totalEntriesCount = document.getElementById('totalEntriesCount');
  if (totalEntriesCount) {
    totalEntriesCount.textContent = segments.length;
  }
}

function renderWheel() {
  if (!wheel) return; // Exit if wheel doesn't exist (rolling only mode)

  console.log('Rendering wheel with', segments.length, 'segments');
  wheel.innerHTML = '';
  const n = Math.max(1, segments.length);
  const sweep = 2 * Math.PI / n;

  // Get current wheel size
  const wheelSize = parseInt(wheel.style.width) || 420;
  const radius = wheelSize / 2;
  const textRadius = radius * 0.7; // Position text at 70% of radius for better visibility

  // Calculate dynamic font size based on number of segments
  let fontSize;
  if (n <= 8) {
    fontSize = 16;
  } else if (n <= 20) {
    fontSize = Math.max(12, 16 - (n - 8) * 0.3);
  } else if (n <= 50) {
    fontSize = Math.max(10, 12 - (n - 20) * 0.1);
  } else if (n <= 100) {
    fontSize = Math.max(8, 10 - (n - 50) * 0.04);
  } else if (n <= 200) {
    fontSize = Math.max(6, 8 - (n - 100) * 0.02);
  } else {
    fontSize = Math.max(4, 6 - (n - 200) * 0.01);
  }

  // Define a color palette similar to the image
  const colorPalette = [
    '#90EE90', // Light Green
    '#FFFF00', // Yellow
    '#87CEEB', // Light Blue
    '#9370DB', // Purple
    '#4169E1', // Dark Blue
    '#FF6347', // Red
    '#FFA500', // Orange
    '#FF69B4', // Hot Pink
    '#32CD32', // Lime Green
    '#FFD700', // Gold
    '#00CED1', // Dark Turquoise
    '#FF1493', // Deep Pink
  ];

  for (let i = 0; i < n; i++) {
    const start = i * sweep, end = start + sweep;
    const x1 = radius * Math.cos(start), y1 = radius * Math.sin(start);
    const x2 = radius * Math.cos(end),   y2 = radius * Math.sin(end);
    const large = sweep > Math.PI ? 1 : 0;

    // Use color from palette or fallback to segment color
    const segmentColor = segments[i]?.color || colorPalette[i % colorPalette.length];

    const path = document.createElementNS('http://www.w3.org/2000/svg','path');
    path.setAttribute('d', `M 0 0 L ${x1} ${y1} A ${radius} ${radius} 0 ${large} 1 ${x2} ${y2} Z`);
    path.setAttribute('fill', segmentColor);
    path.setAttribute('stroke', '#fff');
    path.setAttribute('stroke-width', '1');
    wheel.appendChild(path);

    // Only show text if segment is large enough to be readable
    if (sweep > 0.05) { // Only show text if segment is at least 0.05 radians (about 3 degrees)
      const mx = textRadius * Math.cos(start + sweep/2), my = textRadius * Math.sin(start + sweep/2);
      const text = document.createElementNS('http://www.w3.org/2000/svg','text');
      text.setAttribute('x', mx); text.setAttribute('y', my);

      // Determine text color based on background color brightness
      const isLight = isLightColor(segmentColor);
      text.setAttribute('fill', isLight ? '#000' : '#fff');
      text.setAttribute('font-size', fontSize);
      text.setAttribute('font-weight', 'bold');
      text.setAttribute('text-anchor','middle');
      text.setAttribute('dominant-baseline','middle');
      text.setAttribute('transform', `rotate(${(start + sweep/2) * 180/Math.PI}, ${mx}, ${my})`);

      // Show full name without any truncation
      text.textContent = segments[i]?.label || '';
      wheel.appendChild(text);
    }
  }

  console.log('Wheel rendered with', n, 'segments');
}

// Helper function to determine if a color is light or dark
function isLightColor(color) {
  // Convert hex to RGB
  const hex = color.replace('#', '');
  const r = parseInt(hex.substr(0, 2), 16);
  const g = parseInt(hex.substr(2, 2), 16);
  const b = parseInt(hex.substr(4, 2), 16);

  // Calculate brightness
  const brightness = (r * 299 + g * 587 + b * 114) / 1000;
  return brightness > 128;
}

function startRolling() {
  if (!segments.length) return;
  if (rollingTimer) clearInterval(rollingTimer);

  // Start with a random segment
  rollingIndex = Math.floor(Math.random() * segments.length);
  if (rollerText) {
    rollerText.textContent = segments[rollingIndex].label;
  }

  // Start with slow rolling
  let rollingSpeed = 200; // Start slow
  rollingTimer = setInterval(() => {
    if (segments.length === 0) return;
    rollingIndex = (rollingIndex + 1) % segments.length;
    if (rollerText) {
      rollerText.textContent = segments[rollingIndex].label;
    }
  }, rollingSpeed);
}

function stopRolling(winner) {
  if (rollingTimer) {
    clearInterval(rollingTimer);
    rollingTimer = null;
  }
  if (rollerText) {
    rollerText.textContent = winner?.label || '';
  }

  // Add zoom animation to rolling box (only if rolling box exists)
  if (winner && rollingBox) {
    rollingBox.classList.add('zoom');
    if (rollerText) {
      rollerText.classList.add('zoom');
    }

    // Remove zoom after animation
    setTimeout(() => {
      if (rollingBox) {
        rollingBox.classList.remove('zoom');
      }
      if (rollerText) {
        rollerText.classList.remove('zoom');
      }
    }, 300);
  }
}

function startCountdown(duration) {
  const countdownElement = document.getElementById('countdownTimer');
  const countdownText = document.getElementById('countdownText');

  countdownElement.style.display = 'block';
  let timeLeft = duration;

  // Update countdown immediately
  updateCountdownDisplay(timeLeft);

  countdownTimer = setInterval(() => {
    timeLeft--;
    updateCountdownDisplay(timeLeft);

    if (timeLeft <= 0) {
      clearInterval(countdownTimer);
      countdownTimer = null;
    }
  }, 1000);
}

function updateCountdownDisplay(seconds) {
  const countdownText = document.getElementById('countdownText');
  if (seconds > 0) {
    countdownText.textContent = `${seconds}s`;
  } else {
    countdownText.textContent = '0s';
  }
}

function stopCountdown() {
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
  const countdownElement = document.getElementById('countdownTimer');
  countdownElement.style.display = 'none';
}

function createRealisticSpeedCurve(duration, targetRotation, winnerData) {
  const totalDuration = duration * 1000; // Convert to milliseconds
  const fastPhase = totalDuration * 0.6; // 60% - fast spinning
  const slowPhase = totalDuration * 0.4; // 40% - slowing down

  let startTime = Date.now();
  let startRotation = currentRotation;
  let lastTickTime = 0;

  // Function to update rolling text - cycle through names like wheelofnames.com
  function updateRollingText() {
    if (segments.length === 0) return;

    // Calculate current wheel rotation based on elapsed time
    const elapsed = Date.now() - startTime;
    const progress = Math.min(elapsed / totalDuration, 1);

    // Use easing function to match CSS transition exactly
    const easedProgress = easeInOutCubic(progress);
    const currentRot = startRotation + (targetRotation - startRotation) * easedProgress;

      // During fast spinning phase, cycle through names rapidly
      if (progress < 0.9) {
        // Fast cycling through all names like wheelofnames.com
        rollingIndex = (rollingIndex + 1) % segments.length;
        if (rollerText) {
          rollerText.textContent = segments[rollingIndex].label;
        }

        // Play tick sound synchronized with name changes
        if (elapsed - lastTickTime >= 50) { // Match the 50ms interval
          playTickSound();
          lastTickTime = elapsed;
        }
      } else {
        // In final phase, show the winner determined by the backend
        if (winnerData && winnerData.winner && winnerData.winner.label && rollerText) {
          rollerText.textContent = winnerData.winner.label;
        }

        // Play final tick sound
        if (elapsed - lastTickTime >= 50) {
          playTickSound();
          lastTickTime = elapsed;
        }
      }
  }

  // Start immediately with very fast updates for rolling effect like wheelofnames.com
  if (rollingTimer) {
    clearInterval(rollingTimer);
  }
  rollingTimer = setInterval(updateRollingText, 50); // Fast rolling updates

  // Start rolling audio effect
  startRollingAudio(100); // Play rolling sound every 100ms

  // Start slowing down
  setTimeout(() => {
    if (rollingTimer) {
      clearInterval(rollingTimer);
      rollingTimer = setInterval(updateRollingText, 80); // Slower rolling
    }
  }, fastPhase * 0.6); // Start slowing at 60% of fast phase

  // Even slower
  setTimeout(() => {
    if (rollingTimer) {
      clearInterval(rollingTimer);
      rollingTimer = setInterval(updateRollingText, 120); // Much slower rolling
    }
  }, fastPhase * 0.8); // Start slowing more at 80% of fast phase

  // Very slow before stop
  setTimeout(() => {
    if (rollingTimer) {
      clearInterval(rollingTimer);
      rollingTimer = setInterval(updateRollingText, 200); // Very slow rolling
    }
  }, fastPhase + slowPhase * 0.3); // Start final slowdown at 70% of total time

  // Stop rolling timer at exactly the same time as CSS transition
  // Add a small buffer to ensure the wheel has fully stopped
  setTimeout(() => {
    if (rollingTimer) {
      clearInterval(rollingTimer);
      rollingTimer = null;
    }

    // Stop rolling audio effect
    stopRollingAudio();

    // Ensure the final rolling text shows the correct winner from backend
    // Use the winner that was already determined by the backend instead of calculating
    if (winnerData && winnerData.winner && winnerData.winner.label && rollerText) {
      rollerText.textContent = winnerData.winner.label;
    }
  }, totalDuration + 50); // Add 50ms buffer to ensure wheel has stopped
}

function easeInOutCubic(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}


async function spin() {
  spinBtn.disabled = true;

  const res = await fetch('/api/spin', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }});
  const data = await res.json();
  segments = data.segments || [];
  const n = Math.max(1, segments.length);
  const idx = segments.findIndex(s => s.id === data.winner?.id);
  const sliceAngle = 360 / n;
  // Calculate target angle so the pointer points to the winner
  // The pointer is at the right side (90 degrees), so we need to position the winner at the right
  const targetAngle = idx >= 0 ? (idx * sliceAngle - 90) : (data.angle ?? 0);
  const spins = 6 * 360;
  currentRotation = currentRotation + spins + targetAngle; // Positive for clockwise rotation

  // Start realistic speed curve animation for rolling names (this handles all rolling)
  createRealisticSpeedCurve(currentSpinDuration, currentRotation, data);

  // Start countdown timer
  startCountdown(currentSpinDuration);

  // Apply wheel rotation (clockwise) - CSS transition handles the animation
  if (wheel) {
    wheel.style.transform = `rotate(${currentRotation - 90}deg)`;
  }

  // Stop rolling animation at exactly the same time the wheel stops
  // Add buffer to match the rolling timer buffer
  setTimeout(() => {
    winnerLabel.textContent = `Winner: ${data.winner?.label ?? ''}`;
    currentWinner = data.winner;
    stopCountdown();

    // Show winner modal after a brief delay to ensure wheel and rolling have fully stopped
    setTimeout(() => {
      // Add a brief visual pause to show the final result (only if rolling text exists)
      if (rollerText) {
        rollerText.style.fontWeight = 'bold';
        rollerText.style.color = '#ff6b35';
      }

      setTimeout(() => {
        showWinner(data.winner, idx);
        spinBtn.disabled = false;
      }, 300); // Additional 300ms to show the final result clearly
    }, 200); // 200ms delay to ensure everything has stopped
  }, currentSpinDuration * 1000 + 50); // Add 50ms buffer to match rolling timer
}

function showWinner(winner, winnerIndex) {
  if (!winner) return;
  document.getElementById('winnerName').textContent = winner.label;
  document.getElementById('winnerName').style.color = winner.color;
  document.getElementById('winnerModal').style.display = 'grid';

  // Show arrow pointing to winner (only if wheel exists)
  if (winnerIndex >= 0 && wheel) {
    const n = segments.length;
    const sliceAngle = 360 / n;
    const winnerAngle = (winnerIndex + 0.5) * sliceAngle;
    const arrow = document.getElementById('winnerArrow');
    const wheelRect = document.getElementById('wheel').getBoundingClientRect();
    const centerX = wheelRect.width / 2;
    const centerY = wheelRect.height / 2;
    const radius = wheelRect.width / 2 * 0.8; // Use 80% of wheel radius for arrow position

    const arrowX = centerX + Math.cos((winnerAngle - 90) * Math.PI / 180) * radius;
    const arrowY = centerY + Math.sin((winnerAngle - 90) * Math.PI / 180) * radius;

    arrow.style.left = arrowX + 'px';
    arrow.style.top = arrowY + 'px';
    arrow.style.display = 'block';
  }

  createConfetti();
}

function closeWinnerModal() {
  document.getElementById('winnerModal').style.display = 'none';
  const arrow = document.getElementById('winnerArrow');
  if (arrow) {
    arrow.style.display = 'none';
  }
}

async function recordWinner() {
  if (!currentWinner) return;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const response = await fetch('/winners', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        participant_id: currentWinner.id,
        winner_name: currentWinner.label,
        winner_color: currentWinner.color,
        spin_angle: currentRotation % 360,
        pool_snapshot: segments
      })
    });

    if (response.ok) {
      const result = await response.json();
      alert(result.message || 'Winner recorded successfully!');
      closeWinnerModal();

      // Refresh the wheel segments to remove the winner from the wheel
      await fetchSegments();
    } else {
      const error = await response.json();
      alert('Error recording winner: ' + (error.message || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error recording winner:', error);
    alert('Error recording winner. Please try again.');
  }
}

function createConfetti() {
  const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
  for (let i = 0; i < 50; i++) {
    const confetti = document.createElement('div');
    confetti.className = 'confetti';
    confetti.style.left = Math.random() * 100 + '%';
    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
    confetti.style.animationDelay = Math.random() * 3 + 's';
    confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
    document.body.appendChild(confetti);
    setTimeout(() => confetti.remove(), 5000);
  }
}

spinBtn.addEventListener('click', spin);
fetchSegments();

// Handle window resize to recalculate wheel size
window.addEventListener('resize', () => {
  if (segments.length > 0) {
    updateWheelSize();
    renderWheel();
  }
});

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
  // Initialize audio system
  initAudio();

  updateWheelSize();
  fetchSegments();

  // Update wheel CSS transition to use current spin duration
  const wheel = document.getElementById('wheel');
  wheel.style.transition = `transform ${currentSpinDuration}s cubic-bezier(0.25, 0.46, 0.45, 0.94)`;
});
</script>
</body>
</html>



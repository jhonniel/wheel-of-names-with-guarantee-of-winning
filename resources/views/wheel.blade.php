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
    </style>
    @csrf
    @routes
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
    <div class="rolling-box"><span id="rollerText" class="rolling-text"></span></div>
    <div class="wheel-container">
        <svg id="wheel" viewBox="-210 -210 420 420"></svg>
        <div id="winnerArrow" class="winner-arrow" style="display: none;"></div>
    </div>
    <div>
        <button id="spinBtn">Spin</button>
        <span id="winnerLabel"></span>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        @auth
            <a href="/participants" style="color: #2196F3; text-decoration: none; font-weight: 600; padding: 8px 16px; border: 2px solid #2196F3; border-radius: 6px; display: inline-block; transition: all 0.3s ease; margin-right: 10px;">
                ⚙️ Configure Participants & Spin Duration
            </a>
            <a href="/dashboard" style="color: #28a745; text-decoration: none; font-weight: 600; padding: 8px 16px; border: 2px solid #28a745; border-radius: 6px; display: inline-block; transition: all 0.3s ease; margin-right: 10px;">
                📊 Dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="color: #dc3545; text-decoration: none; font-weight: 600; padding: 8px 16px; border: 2px solid #dc3545; border-radius: 6px; background: none; cursor: pointer; transition: all 0.3s ease;">
                    🚪 Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" style="color: #2196F3; text-decoration: none; font-weight: 600; padding: 8px 16px; border: 2px solid #2196F3; border-radius: 6px; display: inline-block; transition: all 0.3s ease;">
                🔐 Login to Configure
            </a>
        @endauth
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
                <button onclick="deleteWinner()" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Delete Winner</button>
                <button onclick="keepWinner()" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Keep Winner</button>
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

let segments = [];
let currentRotation = 0;
let rollingTimer = null;
let rollingIndex = 0;
let currentWinner = null;
let countdownTimer = null;
let currentSpinDuration = {{ $spinDuration }};

async function fetchSegments() {
  try {
    const res = await fetch('/api/wheel');
    const data = await res.json();
    segments = data.segments || [];
    console.log('Fetched segments:', segments.length);
    updateWheelSize();
    renderWheel();
    // Initialize roller with random name if available
    if (segments.length) {
      const randomIndex = Math.floor(Math.random() * segments.length);
      rollerText.textContent = segments[randomIndex].label;
    }
  } catch (error) {
    console.error('Error fetching segments:', error);
  }
}


function updateWheelSize() {
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

  const wheel = document.getElementById('wheel');
  wheel.style.width = size + 'px';
  wheel.style.height = size + 'px';

  // Update viewBox to match size
  const halfSize = size / 2;
  wheel.setAttribute('viewBox', `-${halfSize} -${halfSize} ${size} ${size}`);
}

function renderWheel() {
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
  rollerText.textContent = segments[rollingIndex].label;

  // Start with slow rolling
  let rollingSpeed = 200; // Start slow
  rollingTimer = setInterval(() => {
    if (segments.length === 0) return;
    rollingIndex = (rollingIndex + 1) % segments.length;
    rollerText.textContent = segments[rollingIndex].label;
  }, rollingSpeed);
}

function stopRolling(winner) {
  if (rollingTimer) {
    clearInterval(rollingTimer);
    rollingTimer = null;
  }
  rollerText.textContent = winner?.label || '';

  // Add zoom animation to rolling box
  if (winner) {
    rollingBox.classList.add('zoom');
    rollerText.classList.add('zoom');

    // Remove zoom after animation
    setTimeout(() => {
      rollingBox.classList.remove('zoom');
      rollerText.classList.remove('zoom');
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

function createRealisticSpeedCurve(duration, targetRotation) {
  const totalDuration = duration * 1000; // Convert to milliseconds
  const fastPhase = totalDuration * 0.6; // 60% - fast spinning
  const slowPhase = totalDuration * 0.4; // 40% - slowing down

  let startTime = Date.now();
  let startRotation = currentRotation;

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
      rollerText.textContent = segments[rollingIndex].label;
    } else {
      // In final phase, show the actual segment the pointer is pointing to
      const normalizedAngle = (currentRot % 360 + 360) % 360;
      const segmentAngle = 360 / segments.length;

      // Calculate which segment the pointer is pointing to
      // The pointer is at the right side (90 degrees), so we need to find which segment is at 90 degrees
      const pointerAngle = (normalizedAngle + 90) % 360;
      const currentSegmentIndex = Math.floor(pointerAngle / segmentAngle) % segments.length;

      if (segments[currentSegmentIndex]) {
        rollerText.textContent = segments[currentSegmentIndex].label;
      }
    }
  }

  // Start immediately with very fast updates for rolling effect like wheelofnames.com
  if (rollingTimer) {
    clearInterval(rollingTimer);
  }
  rollingTimer = setInterval(updateRollingText, 50); // Fast rolling updates

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
  setTimeout(() => {
    if (rollingTimer) {
      clearInterval(rollingTimer);
      rollingTimer = null;
    }

    // Ensure the final rolling text shows the correct winner
    const normalizedAngle = (targetRotation % 360 + 360) % 360;
    const segmentAngle = 360 / segments.length;
    const pointerAngle = (normalizedAngle + 90) % 360;
    const finalSegmentIndex = Math.floor(pointerAngle / segmentAngle) % segments.length;

    if (segments[finalSegmentIndex]) {
      rollerText.textContent = segments[finalSegmentIndex].label;
    }
  }, totalDuration);
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
  createRealisticSpeedCurve(currentSpinDuration, currentRotation);

  // Start countdown timer
  startCountdown(currentSpinDuration);

  // Apply wheel rotation (clockwise) - CSS transition handles the animation
  wheel.style.transform = `rotate(${currentRotation - 90}deg)`;

  // Stop rolling animation at exactly the same time the wheel stops
  setTimeout(() => {
    winnerLabel.textContent = `Winner: ${data.winner?.label ?? ''}`;
    currentWinner = data.winner;
    stopCountdown();

    // Show winner modal after a brief delay to ensure wheel and rolling have fully stopped
    setTimeout(() => {
      // Add a brief visual pause to show the final result
      rollerText.style.fontWeight = 'bold';
      rollerText.style.color = '#ff6b35';

      setTimeout(() => {
        showWinner(data.winner, idx);
        spinBtn.disabled = false;
      }, 300); // Additional 300ms to show the final result clearly
    }, 200); // 200ms delay to ensure everything has stopped
  }, currentSpinDuration * 1000);
}

function showWinner(winner, winnerIndex) {
  if (!winner) return;
  document.getElementById('winnerName').textContent = winner.label;
  document.getElementById('winnerName').style.color = winner.color;
  document.getElementById('winnerModal').style.display = 'grid';

  // Show arrow pointing to winner
  if (winnerIndex >= 0) {
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
  document.getElementById('winnerArrow').style.display = 'none';
}

function deleteWinner() {
  if (!currentWinner) return;

  if (confirm(`Are you sure you want to permanently delete "${currentWinner.label}" from the participants list?`)) {
    // Create a form to submit the deletion
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/participants/${currentWinner.id}`;
    form.style.display = 'none';

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // Add method spoofing for DELETE
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);

    // Submit the form
    document.body.appendChild(form);
    form.submit();
  }
}

function keepWinner() {
  // Just close the modal, keeping the winner
  closeWinnerModal();
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
  updateWheelSize();
  fetchSegments();

  // Update wheel CSS transition to use current spin duration
  const wheel = document.getElementById('wheel');
  wheel.style.transition = `transform ${currentSpinDuration}s cubic-bezier(0.25, 0.46, 0.45, 0.94)`;
});
</script>
</body>
</html>



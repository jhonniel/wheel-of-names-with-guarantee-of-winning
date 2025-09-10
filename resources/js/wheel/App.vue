<template>
  <main style="display:grid;place-items:center;gap:16px;font-family:system-ui,sans-serif;min-height:100vh;padding:24px;">
    <div class="pointer" style="width:0;height:0;border-left:12px solid transparent;border-right:12px solid transparent;border-bottom:18px solid #111;margin-top:-12px;"></div>
    <svg ref="wheelRef" :style="wheelStyle" viewBox="-210 -210 420 420"></svg>
    <div style="display:flex;gap:12px;align-items:center;">
      <button :disabled="spinning" @click="spin" style="padding:10px 16px;font-weight:600;">{{ spinning ? 'Spinning…' : 'Spin' }}</button>
      <span v-if="winner">Winner: {{ winner.label }}</span>
    </div>
  </main>

</template>

<script setup>
import { onMounted, ref, computed } from 'vue';

const wheelRef = ref(null);
const segments = ref([]);
const spinning = ref(false);
const winner = ref(null);
const currentRotation = ref(0);

const wheelStyle = computed(() => ({
  width: '420px',
  height: '420px',
  transform: `rotate(${-90}deg)`,
  transition: 'transform 4s cubic-bezier(.17,.67,.32,1.28)'
}));

async function fetchSegments() {
  const res = await fetch('/api/wheel');
  const data = await res.json();
  segments.value = data.segments || [];
  renderWheel();
}

function renderWheel() {
  const svg = wheelRef.value;
  if (!svg) return;
  while (svg.firstChild) svg.removeChild(svg.firstChild);
  const n = segments.value.length || 1;
  const sweep = (2 * Math.PI) / n;
  for (let i = 0; i < n; i++) {
    const start = i * sweep, end = start + sweep;
    const x1 = 200 * Math.cos(start), y1 = 200 * Math.sin(start);
    const x2 = 200 * Math.cos(end),   y2 = 200 * Math.sin(end);
    const large = sweep > Math.PI ? 1 : 0;
    const path = document.createElementNS('http://www.w3.org/2000/svg','path');
    path.setAttribute('d', `M 0 0 L ${x1} ${y1} A 200 200 0 ${large} 1 ${x2} ${y2} Z`);
    path.setAttribute('fill', segments.value[i]?.color || '#ccc');
    svg.appendChild(path);

    const mx = 120 * Math.cos(start + sweep/2), my = 120 * Math.sin(start + sweep/2);
    const text = document.createElementNS('http://www.w3.org/2000/svg','text');
    text.setAttribute('x', mx); text.setAttribute('y', my);
    text.setAttribute('fill', '#000'); text.setAttribute('font-size', '12');
    text.setAttribute('text-anchor','middle'); text.setAttribute('dominant-baseline','middle');
    text.setAttribute('transform', `rotate(${(start + sweep/2) * 180/Math.PI}, ${mx}, ${my})`);
    text.textContent = segments.value[i]?.label || '';
    svg.appendChild(text);
  }
}

async function spin() {
  if (spinning.value) return;
  spinning.value = true;
  try {
    const res = await fetch('/api/spin', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }});
    const data = await res.json();
    segments.value = data.segments || [];
    const n = segments.value.length || 1;
    const idx = segments.value.findIndex(s => s.id === data.winner?.id);
    const sliceAngle = 360 / n;
    const targetAngle = idx >= 0 ? (idx + 0.5) * sliceAngle : (data.angle ?? 0);
    const spins = 6 * 360;
    currentRotation.value = currentRotation.value + spins + (360 - targetAngle);
    wheelRef.value.style.transform = `rotate(${currentRotation.value - 90}deg)`;
    setTimeout(() => {
      winner.value = data.winner;
      spinning.value = false;
    }, 4200);
  } catch (e) {
    console.error(e);
    spinning.value = false;
  }
}

onMounted(fetchSegments);
</script>

<style scoped>
button:disabled { opacity: .6; cursor: not-allowed; }
</style>



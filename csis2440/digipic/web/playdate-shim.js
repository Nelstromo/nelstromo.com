// Minimal Playdate API shim for the browser canvas
// Provides: playdate.graphics, sound, timer, ui, datastore, getSystemMenu, input mapping

const canvasDefaultSize = 240;
let canvas, ctx;
let lastTime = performance.now();
let crankAngle = 0; // degrees 0..360

function ensureCtx() {
  if (!canvas) return;
  if (!ctx) ctx = canvas.getContext("2d");
}

// Simple animators to mimic CoreLibs/animator
class Animator {
  constructor(duration, from, to) {
    this._duration = duration; // ms
    this._from = from;
    this._to = to;
    this._start = performance.now();
    this._ended = duration === 0;
  }
  currentValue() {
    if (this._duration === 0) return this._to;
    const t = Math.min(1, (performance.now() - this._start) / this._duration);
    if (t >= 1) this._ended = true;
    return this._from + (this._to - this._from) * t;
  }
  ended() { return this._ended; }
}

// Simple geometry helpers
class LineSegment {
  constructor(x1, y1, x2, y2){ this.x1=x1; this.y1=y1; this.x2=x2; this.y2=y2; }
}
class AffineTransform {
  rotate(angleDeg, cx, cy){
    this._angle = angleDeg * Math.PI/180;
    this._cx = cx; this._cy = cy;
  }
  transformLineSegment(ln){
    const { _angle:a, _cx:cx, _cy:cy } = this;
    const cos = Math.cos(a), sin = Math.sin(a);
    function tf(x,y){
      const dx = x - cx, dy = y - cy;
      return { x: cx + dx*cos - dy*sin, y: cy + dx*sin + dy*cos };
    }
    const p1 = tf(ln.x1, ln.y1), p2 = tf(ln.x2, ln.y2);
    ln.x1 = p1.x; ln.y1 = p1.y; ln.x2 = p2.x; ln.y2 = p2.y;
  }
}

// Graphics API
const graphics = {
  sprite: { update(){} },
  kColorWhite: "white",
  kColorBlack: "black",
  setColor(c){ ensureCtx(); ctx.strokeStyle = c; ctx.fillStyle = c; },
  setPattern(){ /* ignored */ },
  setDitherPattern(){ /* ignored */ },
  setLineWidth(w){ ensureCtx(); ctx.lineWidth = w; },
  clear(){ ensureCtx(); ctx.clearRect(0,0,canvas.width,canvas.height); },
  drawArc(cx, cy, radius, startDeg, endDeg){
    ensureCtx();
    ctx.beginPath();
    ctx.arc(cx, cy, radius, (startDeg-90)*Math.PI/180, (endDeg-90)*Math.PI/180);
    ctx.stroke();
  },
  drawCircleAtPoint(cx, cy, r){ ensureCtx(); ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2); ctx.stroke(); },
  drawCircleInRect(x,y,w,h){
    const r = Math.min(w,h)/2; this.drawCircleAtPoint(x+w/2,y+h/2,r);
  },
  fillCircleInRect(x,y,w,h){ ensureCtx(); const r=Math.min(w,h)/2; ctx.beginPath(); ctx.arc(x+w/2,y+h/2,r,0,Math.PI*2); ctx.fill(); },
  drawLine(ln){ ensureCtx(); ctx.beginPath(); ctx.moveTo(ln.x1, ln.y1); ctx.lineTo(ln.x2, ln.y2); ctx.stroke(); },
  fillRect(x,y,w,h){ ensureCtx(); ctx.fillRect(x,y,w,h); },
  drawImage(img,x,y){ ensureCtx(); ctx.drawImage(img.canvas, x,y); },
  image: class {
    constructor(w,h, background){
      this.canvas = document.createElement("canvas");
      this.canvas.width = w; this.canvas.height = h;
      const c = this.canvas.getContext("2d");
      c.fillStyle = background || "white"; c.fillRect(0,0,w,h);
    }
    draw(x,y){ ctx.drawImage(this.canvas, x,y); }
  },
  pushContext(img){ this._prevCtx = ctx; ctx = img ? img.canvas.getContext("2d") : ctx; },
  popContext(){ if (this._prevCtx) { ctx = this._prevCtx; this._prevCtx = null; } },
  animator: Animator,
};

// Timer API
const timer = {
  updateTimers(){ /* noop for now */ },
};

// UI API
const ui = {
  crankIndicator: {
    start(){},
    update(){},
  }
};

// Geometry namespace
const geometry = { lineSegment: LineSegment, affineTransform: AffineTransform };

// Sound shim
const sound = {
  sampleplayer: {
    new(path){
      const audio = new Audio();
      // naive mapping: try to fetch wav from ../Source/snd
      audio.src = path.replace(/^snd\//, '../Source/snd/') + '.wav';
      return { play: ()=>{ audio.currentTime = 0; audio.play(); } };
    }
  }
};

// Data storage
const datastore = {
  read(key){ try{ return JSON.parse(localStorage.getItem('pd_'+key)); }catch(e){ return null; } },
  write(value, key){ localStorage.setItem('pd_'+key, JSON.stringify(value)); },
  delete(key){ localStorage.removeItem('pd_'+key); }
};

// System menu (no real UI; stubs)
function getSystemMenu(){
  return {
    addMenuItem(label, cb){ /* could map to buttons later */ this[label] = cb; },
    addOptionsMenuItem(label, options, current, onChange){ this[label] = (val)=>onChange(val); }
  };
}

// Input mapping: arrow keys + Space (slot) + Right Shift (undo)
const keyState = new Set();
window.addEventListener('keydown', (e)=>{
  keyState.add(e.code);
  switch(e.code){
    case 'ArrowLeft': window.playdate?.leftButtonDown?.(); break;
    case 'ArrowRight': window.playdate?.rightButtonDown?.(); break;
    case 'ArrowUp': window.playdate?.upButtonDown?.(); break;
    case 'ArrowDown': window.playdate?.downButtonDown?.(); break;
    case 'Space': window.playdate?.AButtonDown?.(); e.preventDefault(); break;
    case 'ShiftRight': window.playdate?.BButtonDown?.(); e.preventDefault(); break;
  }
});
window.addEventListener('keyup', (e)=>{ keyState.delete(e.code); });

// Simple crank emulation with A/D to rotate
window.addEventListener('keydown', (e)=>{
  if (e.code === 'KeyA'){ crankAngle = (crankAngle - 11.25 + 360) % 360; window.playdate?.cranked?.(); }
  if (e.code === 'KeyD'){ crankAngle = (crankAngle + 11.25) % 360; window.playdate?.cranked?.(); }
});

// Global playdate object
window.playdate = {
  graphics,
  geometry,
  sound,
  timer,
  ui,
  datastore,
  getSystemMenu,
  isCrankDocked(){ return false; },
  getCrankPosition(){ return crankAngle; },
};

// Allow web harness to supply canvas
window.__pd_setCanvas = function(cnv){
  canvas = cnv;
  // Scale 240x240 to canvas size via CSS transforms by drawing in 1:1
  // We will render at 240x240 coordinate space and center at 120,120
  canvas.width = 400; canvas.height = 400; // already set; ensure ctx
  ctx = canvas.getContext('2d');
  // translate to center 240x240 in 400x400
  ctx.translate((canvas.width-240)/2, (canvas.height-240)/2);
};

// Drive update loop
function loop(){
  requestAnimationFrame(loop);
  try{ window.playdate?.update?.(); }catch(e){ /* ignore until main loads */ }
}
loop();

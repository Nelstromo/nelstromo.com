
(() => {
  // Elements
  const court   = document.getElementById('pongCourt');
  const paddle  = document.getElementById('pongPaddle');
  const ball    = document.getElementById('pongBall');
  const start   = document.getElementById('pongStart');
  const again   = document.getElementById('pongAgain');
  const overlay = document.getElementById('pongOverlay');

  const timeEl     = document.getElementById('pongTime');
  const bouncesEl  = document.getElementById('pongBounces');
  const creditsEl  = document.getElementById('pongCredits');
  const ovBounces  = document.getElementById('ovBounces');
  const ovCredits  = document.getElementById('ovCredits');

  // Config (easy to tweak)
  const ROUND_SECONDS = 30;
  const PADDLE_W = 110;     // px (matches CSS)
  const PADDLE_H = 12;      // px (matches CSS)
  const BALL_SIZE = 14;     // px (matches CSS)
  const START_SPEED = 280;  // px/s
  const SPEED_INC   = 1.05; // per paddle hit
  const MAX_SPEED   = 820;  // px/s
  const CREDITS_PER_BOUNCE = 1000;
  const STREAK_BONUS_EVERY = 5;
  const STREAK_BONUS_AMT   = 500.00;
  const PADDLE_MARGIN = 12; // bottom margin (matches CSS)

  // State
  let running = false, tPrev = 0, timerId = null;
  let courtRect = null;
  let bx=0, by=0, vx=0, vy=0, speed=START_SPEED;
  let px=0; // paddle center X (px)
  let bounces=0, credits=0, streak=0, timeLeft = ROUND_SECONDS;

  // Helpers
  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  const resetPositions = () => {
    courtRect = court.getBoundingClientRect();
    px = courtRect.width/2;
    setPaddleX(px);
    bx = courtRect.width/2 - BALL_SIZE/2;
    by = courtRect.height*0.28;
    // launch with random direction
    const angle = (Math.random()*0.6 + 0.2) * Math.PI; // ~36°–144°
    speed = START_SPEED;
    vx = Math.cos(angle) * speed * (Math.random()<.5?-1:1);
    vy = Math.sin(angle) * speed;
    place(ball, bx, by);
  };
  const place = (el, x, y) => { el.style.transform = `translate(${x}px, ${y}px)`; };
  const setPaddleX = x => {
    px = clamp(x, PADDLE_W/2 + 4, courtRect.width - PADDLE_W/2 - 4);
    paddle.style.left = px + 'px';
  };

  // Input: pointer (mouse/touch)
  court.addEventListener('pointerdown', e => { court.setPointerCapture(e.pointerId); });
  court.addEventListener('pointermove', e => {
    if (!courtRect) return;
    setPaddleX(e.clientX - courtRect.left);
  });
  // Input: keyboard
  window.addEventListener('keydown', e => {
    if (!running) return;
    const step = 28;
    if (e.key === 'ArrowLeft')  setPaddleX(px - step);
    if (e.key === 'ArrowRight') setPaddleX(px + step);
  });

  // Resize
  const onResize = () => { resetPositions(); };
  window.addEventListener('resize', onResize);

  function startRound() {
    running = true;
    overlay.hidden = true;
    start.disabled = true;
    timeLeft = ROUND_SECONDS;
    timeEl.textContent = timeLeft;
    bounces = credits = streak = 0;
    bouncesEl.textContent = 0;
    creditsEl.textContent = 0;
    resetPositions();
    tPrev = performance.now();
    cancelAnimationFrame(timerId);
    timerId = requestAnimationFrame(tick);

    // countdown
    clearInterval(startRound._iv);
    startRound._iv = setInterval(() => {
      if (!running) return;
      timeLeft--;
      timeEl.textContent = Math.max(0, timeLeft);
      if (timeLeft <= 0) endRound();
    }, 1000);
  }

  function endRound() {
    running = false;
    start.disabled = false;
    cancelAnimationFrame(timerId);
    clearInterval(startRound._iv);
    ovBounces.textContent = bounces;
    ovCredits.textContent = credits;
    overlay.hidden = false;

    fetch('includes/award.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({ game: 'pong', bounces, duration: ROUND_SECONDS })
})
.then(r => r.json())
.then(data => {
  if (data?.ok) {
    ovCredits.textContent = data.earned; // per-round credits granted
    const wallet = document.getElementById('walletTotal');
    if (wallet) wallet.textContent = data.wealth.toLocaleString();
  } else {
    console.warn('Award failed:', data);
  }
})
.catch(console.error);

  }

  function tick(tNow) {
    const dt = Math.min(0.032, (tNow - tPrev) / 1000); // clamp dt to ~32ms
    tPrev = tNow;

    // Move ball
    bx += vx * dt;
    by += vy * dt;

    // Wall collisions (left/right)
    if (bx <= 0) { bx = 0; vx = Math.abs(vx); }
    else if (bx + BALL_SIZE >= courtRect.width) { bx = courtRect.width - BALL_SIZE; vx = -Math.abs(vx); }

    // Ceiling
    if (by <= 0) { by = 0; vy = Math.abs(vy); }

    // Paddle collision
    const paddleTop = courtRect.height - (PADDLE_MARGIN + PADDLE_H);
    if (vy > 0 && by + BALL_SIZE >= paddleTop) {
      const ballCenter = bx + BALL_SIZE/2;
      const half = PADDLE_W/2;
      if (ballCenter >= px - half && ballCenter <= px + half) {
       
        const hit = (ballCenter - px) / half;     
        const ang = hit * (Math.PI/3);            
        speed = Math.min(MAX_SPEED, speed * SPEED_INC);
        const svy = Math.cos(ang) * speed;        
        const svx = Math.sin(ang) * speed;
        vy = -Math.abs(svy);
        vx = svx;

        by = paddleTop - BALL_SIZE - 0.5; 

        // Rewards
        bounces++; streak++;
        const add = CREDITS_PER_BOUNCE + (streak % STREAK_BONUS_EVERY === 0 ? STREAK_BONUS_AMT : 0);
        credits += add;

        bouncesEl.textContent = bounces;
        creditsEl.textContent = credits;
      } else if (by > paddleTop + 8) {
        
        streak = 0;
        vy = -Math.abs(vy) * 0.9;
      }
    }

   
    if (by + BALL_SIZE >= courtRect.height) {
      by = courtRect.height - BALL_SIZE;
      vy = -Math.abs(vy) * 0.9;
      streak = 0;
    }

    place(ball, bx, by);
    if (running) timerId = requestAnimationFrame(tick);
  }

  // Buttons
  start.addEventListener('click', () => { if (!running) startRound(); });
  again?.addEventListener('click', () => startRound());

  // Init
  resetPositions();
})();


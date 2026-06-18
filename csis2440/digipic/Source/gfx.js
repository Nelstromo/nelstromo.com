// JavaScript port of Source/gfx.lua
// Exports: animateRingChange, gfxNeedUpdate, startLevelGfx, animateRotation, gfxUpdate

// Assumes a Playdate-like global `playdate` API is available

const gfx = playdate.graphics;

const RING_CENTER = { x: 120, y: 120 };
// These tables are 1-based to match original Lua math for ring geometry
const RING_WIDTHS = [null, -1, 10, 8, 6, 5];
const RING_RADIUS = [null, 114, 95, 82, 71, 62];
const CHUNK_SIZE = [null, -1, 0.4, 0.45, 0.5, 0.5];
const PIN_SIZE = [
  null,
  [-10, 5],
  [-7, 5],
  [-6, 4],
  [-5, 4],
];
const SEGMENT_SIZE = 11.25;
const SEGMENT_HALFSIZE = SEGMENT_SIZE / 2;
const PIN_WIDTH = 6;
const SMOL_PIN_WIDTH = 3;

let rotateAnimator = new gfx.animator(0, 0, 0);
let ringAnimator = new gfx.animator(0, 0, 0);
let needToDraw = true;
let ringsImage = null;
let clearAllPicks = false;

export function startLevelGfx(game) {
  rotateAnimator = new gfx.animator(0, 0, game.currentOffset * SEGMENT_SIZE);
  // game.ring is 0-based; ring geometry arrays are 1-based
  ringAnimator = new gfx.animator(0, RING_RADIUS[game.ring + 1], RING_RADIUS[game.ring + 1]);
  ringsImage = null;
  needToDraw = true;
  clearAllPicks = true;
}

export function animateRingChange(from, to) {
  // from/to are 0-based; ring geometry arrays are 1-based
  ringAnimator = new gfx.animator(100, RING_RADIUS[from + 1], RING_RADIUS[to + 1]);
  needToDraw = true;
}

export function animateRotation(dstPos) {
  let dstAngle = dstPos * SEGMENT_SIZE;
  let currentAngle = rotateAnimator.currentValue();
  if (currentAngle < 0) {
    currentAngle = currentAngle + 360;
  }
  if (currentAngle < 90 && dstAngle > 270) {
    dstAngle = dstAngle - 360;
  } else if (currentAngle > 270 && dstAngle < 90) {
    currentAngle = currentAngle - 360;
  }
  rotateAnimator = new gfx.animator(40, currentAngle, dstAngle);
  needToDraw = true;
}

function drawSegment(ringIndex1Based, segment) {
  gfx.setLineWidth(RING_WIDTHS[ringIndex1Based]);
  const startAngle = -SEGMENT_HALFSIZE + (segment * SEGMENT_SIZE) + CHUNK_SIZE[ringIndex1Based];
  const endAngle = SEGMENT_HALFSIZE + (segment * SEGMENT_SIZE) - CHUNK_SIZE[ringIndex1Based];
  gfx.drawArc(RING_CENTER.x, RING_CENTER.y, RING_RADIUS[ringIndex1Based], startAngle, endAngle);
}

function drawPin(ringIndex1Based, angle) {
  const my = RING_CENTER.y - RING_RADIUS[ringIndex1Based];
  const pinSize = PIN_SIZE[ringIndex1Based];
  const ln = new playdate.geometry.lineSegment(
    RING_CENTER.x,
    my - pinSize[0],
    RING_CENTER.x,
    my - pinSize[1]
  );
  const transform = new playdate.geometry.affineTransform();
  transform.rotate(angle, RING_CENTER.x, RING_CENTER.y);
  transform.transformLineSegment(ln);
  gfx.drawLine(ln);
}

function drawPick(pick, ringIndex0Based, crank) {
  // convert to 1-based for geometry tables
  const r = ringIndex0Based + 1;
  // pins
  if (r <= 4 && pick != null) {
    gfx.setLineWidth(PIN_WIDTH);
    for (const value of pick) {
      drawPin(r, (value * SEGMENT_SIZE) + crank);
    }
  }
  // outer ring
  gfx.setColor(gfx.kColorWhite);
  gfx.setLineWidth(1.5);
  gfx.drawCircleAtPoint(RING_CENTER.x, RING_CENTER.y, 1 + ringAnimator.currentValue());
  gfx.setColor(gfx.kColorBlack);
  gfx.setPattern([0x55, 0xaa, 0x55, 0xaa, 0x55, 0xaa, 0x55, 0xaa]);
  gfx.drawCircleAtPoint(RING_CENTER.x, RING_CENTER.y, ringAnimator.currentValue());
}

const KEY_MENU = {
  cols: 3,
  x: 245,
  y: 20,
  dx: 50,
  dy: 50,
};

function drawSmolPick(pick, index1Based, selected, currentOffset) {
  const col = (index1Based - 1) % 3;
  const row = Math.floor((index1Based - 1) / 3);
  const x = KEY_MENU.x + (col * KEY_MENU.dx) + 1;
  const y = KEY_MENU.y + (row * KEY_MENU.dy) + 1;
  if (pick != null) {
    const cx = x + 24;
    const cy = y + 24;
    let offset = 0;
    if (selected) {
      gfx.setColor(gfx.kColorWhite);
      gfx.fillRect(x, y, 50, 50);
      gfx.setColor(gfx.kColorBlack);
      gfx.fillCircleInRect(x + 14, y + 14, 20, 20);
      offset = currentOffset;
    }
    gfx.setLineWidth(1);
    gfx.drawCircleInRect(x + 4, y + 4, 40, 40);
    for (const bit of pick) {
      const ln = new playdate.geometry.lineSegment(cx, cy - 14, cx, cy - 24);
      const transform = new playdate.geometry.affineTransform();
      transform.rotate((bit + offset) * SEGMENT_SIZE, cx, cy);
      transform.transformLineSegment(ln);
      gfx.setLineWidth(SMOL_PIN_WIDTH);
      gfx.drawLine(ln);
    }
  }
}

function drawPickMenu(game) {
  if (clearAllPicks) {
    // draw all picks
    clearAllPicks = false;
    gfx.setColor(gfx.kColorWhite);
    gfx.fillRect(KEY_MENU.x, KEY_MENU.y, 150, 200);
    gfx.setColor(gfx.kColorBlack);
    for (let i = 1; i <= 12; i++) {
      drawSmolPick(game.picks[i - 1], i, i === game.pick + 1, game.currentOffset);
    }
  } else {
    // clear and draw selected
    drawSmolPick(game.picks[game.pick], game.pick + 1, true, game.currentOffset);
  }
}

function drawRing(index1Based, value) {
  for (let bit = 0; bit < 32; bit++) {
    if (((1 << bit) & value) !== 0) {
      drawSegment(index1Based, bit);
    }
  }
}

function drawRingsImage(rings, currentRing0Based) {
  const img = new gfx.image(240, 240, gfx.kColorWhite);
  gfx.pushContext(img);
  gfx.setColor(gfx.kColorBlack);
  // inner rings (deeper than current)
  for (let r = currentRing0Based + 1; r <= rings.length; r++) {
    gfx.setDitherPattern((r - currentRing0Based - 1) * 0.25);
    drawRing(r, rings[r - 1]);
  }
  gfx.popContext();
  return img;
}

export function gfxNeedUpdate(redrawRings, redrawPicks) {
  needToDraw = true;
  if (redrawRings) {
    ringsImage = null;
  }
  if (redrawPicks) {
    clearAllPicks = true;
  }
}

export function gfxUpdate(game) {
  if (!needToDraw) return;

  // draw rings
  if (ringsImage == null) {
    ringsImage = drawRingsImage(game.rings, game.ring);
  }
  ringsImage.draw(0, 0);
  gfx.setColor(gfx.kColorBlack);

  const ringAnimatorEnded = ringAnimator.ended();
  if (ringAnimatorEnded) {
    // current pick
    drawPick(game.picks[game.pick], game.ring, rotateAnimator.currentValue());
    if (rotateAnimator.ended()) {
      needToDraw = false;
    }
  } else {
    // animating ring
    gfx.setLineWidth(1);
    gfx.drawCircleAtPoint(RING_CENTER.x, RING_CENTER.y, ringAnimator.currentValue());
  }

  drawPickMenu(game);
}

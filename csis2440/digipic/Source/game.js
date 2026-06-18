// JavaScript port of Source/game.lua
// Exports: createLevel, fitTest, offsetPick, bitsToValue

const UINT32_MAX = 0xFFFFFFFF >>> 0;

// levels always have 2 rings per pick, and optionally random picks
const DIFFICULTY_LEVEL = [
  // [0] novice
  {
    rings: 2,
    randomPicks: { count: 0 },
    pick: [2, 2, 2, 3], // pick size distribution
  },
  // [1] advanced
  {
    rings: 2,
    randomPicks: { count: 2, sizes: [2, 3] },
    pick: [2, 2, 2, 3, 3, 4],
  },
  // [2] expert
  {
    rings: 3,
    randomPicks: { count: 3, sizes: [1, 2, 2, 3, 3] },
    pick: [1, 2, 3, 4],
  },
  // [3] master
  {
    rings: 4,
    randomPicks: { count: 4, sizes: [1, 2, 2, 2, 3, 3, 3] },
    pick: [1, 2, 3, 4],
  },
];

// keep generated picks in order and not rotate them
const CHEATING = false;

export function bitsToValue(bits) {
  let value = 0 >>> 0;
  for (const bit of bits) {
    value = (value | (1 << bit)) >>> 0;
  }
  return value >>> 0;
}

export function valueToBits(value) {
  const bits = [];
  for (let bit = 0; bit < 32; bit++) {
    if (((value >>> bit) & 1) !== 0) {
      bits.push(bit);
    }
  }
  return bits;
}

export function fitTest(ring, pick) {
  const pickValue = bitsToValue(pick) >>> 0;
  if (pickValue === 0) return false;
  const holes = (~ring) >>> 0;
  const filled = (pickValue & holes) >>> 0;
  return filled === pickValue;
}

function mod32(n) {
  return ((n % 32) + 32) % 32;
}

export function offsetPick(pick, by) {
  const newPick = new Array(pick.length);
  for (let i = 0; i < pick.length; i++) {
    newPick[i] = mod32(pick[i] + by);
  }
  return newPick;
}

function randInt(min, max) {
  // inclusive
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function makePick(size, ring) {
  const pick = [];
  while (pick.length < size) {
    const availableBits = valueToBits(ring & 0x55555555);
    if (availableBits.length === 0) break;
    const newBit = availableBits[randInt(0, availableBits.length - 1)];
    pick.push(newBit);
  }
  return pick;
}

function shuffleInPlace(arr) {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
}

export function createLevel(difficulty) {
  const rings = [];
  const picks = [];

  const setup = DIFFICULTY_LEVEL[difficulty];

  // create initial rings
  while (rings.length < setup.rings) {
    rings.push(UINT32_MAX);
  }

  // set up picks: 2 picks per ring
  for (let r = 0; r < setup.rings; r++) {
    for (let p = 0; p < 2; p++) {
      // choose size of pick
      let pickSize = setup.pick[randInt(0, setup.pick.length - 1)];
      // if last size was 1, choose a different one
      if (difficulty >= 2 && pickSize === 1 && picks.length > 0 && picks[picks.length - 1].length === 1) {
        pickSize = randInt(2, 3);
      }

      const pick = makePick(pickSize, rings[r]);
      rings[r] = (rings[r] ^ bitsToValue(pick)) >>> 0;
      // randomly rotate pick
      if (CHEATING) {
        picks.push(pick);
      } else {
        picks.push(offsetPick(pick, randInt(1, 32)));
      }
    }
  }

  // add random picks
  for (let p = 0; p < setup.randomPicks.count; p++) {
    const sizes = setup.randomPicks.sizes || [1];
    const pickSize = sizes[randInt(0, sizes.length - 1)];
    const pick = makePick(pickSize, UINT32_MAX);
    picks.push(pick);
  }

  if (!CHEATING) {
    shuffleInPlace(picks);
  }

  return { picks, rings };
}

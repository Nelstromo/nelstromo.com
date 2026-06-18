// JavaScript rewrite of Source/main.lua
// Assumes a Playdate-like global `playdate` API is available

import { createLevel, fitTest, offsetPick, bitsToValue } from "./game.js";
import { animateRingChange, gfxNeedUpdate, startLevelGfx, animateRotation, gfxUpdate } from "./gfx.js";

const SEGMENT_SIZE = 11.25;
const DIFFICULTY_LEVELS = ["novice", "advanced", "expert", "master"];
const UINT32_MAX = 0xFFFFFFFF >>> 0;

const game = {
  pick: 0, // selected pick (0-based)
  ring: 0, // currently operating ring (0-based)
  currentOffset: 0, // [0, 31]
  picks: [], // list of pins
  rings: [] // 32-bit integers
};

let undoStack = [];
let settings = {
  level: 3 // difficulty [0,3]
};

const sounds = {};

function nextRing() {
  if (game.ring < game.rings.length - 1) {
    animateRingChange(game.ring, game.ring + 1);
    game.ring = game.ring + 1;
  }
}

function startLevel(picks, rings) {
  game.pick = 0;
  game.ring = 0;
  game.picks = picks || [];
  game.currentOffset = Math.floor(playdate.getCrankPosition() / SEGMENT_SIZE);
  if (game.picks[game.pick]) {
    game.picks[game.pick] = offsetPick(game.picks[game.pick], -game.currentOffset);
  }
  game.rings = rings || [];
  undoStack = [];
}

function choosePick(newPick) {
  if (newPick == null) {
    newPick = game.pick;
    if (game.picks.length > 0) {
      let guard = 0;
      while ((!game.picks[newPick] || game.picks[newPick].length === 0) && guard < game.picks.length) {
        newPick = (newPick + 1) % game.picks.length;
        guard++;
      }
    }
  }
  if (game.picks[game.pick]) {
    game.picks[game.pick] = offsetPick(game.picks[game.pick], game.currentOffset);
  }
  if (game.picks[newPick]) {
    game.picks[newPick] = offsetPick(game.picks[newPick], -game.currentOffset);
  }
  game.pick = newPick;
  gfxNeedUpdate(false, true);
  sounds.change?.play();
}

function levelIsComplete() {
  const last = game.rings.length - 1;
  return last >= 0 && (game.rings[last] >>> 0) === UINT32_MAX;
}

function doSlot(pick) {
  sounds.slot?.play();
  gfxNeedUpdate(true);
  undoStack.push({
    selectedRing: game.ring,
    selectedPick: game.pick,
    pick: offsetPick(game.picks[game.pick], game.currentOffset),
    ring: game.rings[game.ring]
  });
  game.rings[game.ring] = (game.rings[game.ring] | bitsToValue(pick)) >>> 0;
  game.picks[game.pick] = [];
  if (((game.rings[game.ring] >>> 0) === UINT32_MAX)) {
    nextRing();
  }
  if (levelIsComplete()) {
    game.pick = -1;
    sounds.unlock?.play();
    gfxNeedUpdate(true, true);
  } else {
    choosePick();
  }
}

function canUndo() {
  return undoStack.length > 0 && !levelIsComplete();
}

function undo() {
  sounds.unslot?.play();
  const state = undoStack.pop();
  if (!state) return;
  if (game.ring !== state.selectedRing) {
    animateRingChange(game.ring, state.selectedRing);
    game.ring = state.selectedRing;
  }
  game.rings[state.selectedRing] = state.ring;
  if (state.selectedPick !== game.pick && game.picks[game.pick]) {
    game.picks[game.pick] = offsetPick(game.picks[game.pick], game.currentOffset);
  }
  game.picks[state.selectedPick] = offsetPick(state.pick, -game.currentOffset);
  game.pick = state.selectedPick;
  gfxNeedUpdate(true, true);
}

function changePick(change) {
  if (levelIsComplete()) {
    return;
  }
  const newPick = game.pick + change;
  if (newPick >= 0 && newPick < game.picks.length) {
    choosePick(newPick);
  }
}

function isPickSelected() {
  const pick = game.picks[game.pick] || [];
  return pick.length !== 0;
}

function startNewGame() {
  const { picks, rings } = createLevel(settings.level);
  startLevel(picks, rings);
  startLevelGfx(game);
  sounds.start?.play();
}

playdate.cranked = function (_, _) {
  const crank = playdate.getCrankPosition();
  const dstPos = Math.floor(crank / SEGMENT_SIZE);
  if (dstPos !== game.currentOffset && isPickSelected()) {
    game.currentOffset = dstPos;
    animateRotation(dstPos);
    sounds.click?.play();
  }
};

playdate.AButtonDown = function () {
  if (levelIsComplete()) {
    startNewGame();
    return;
  }
  const pick = offsetPick(game.picks[game.pick], game.currentOffset);
  if (fitTest(game.rings[game.ring], pick)) {
    doSlot(pick);
  }
};

playdate.BButtonDown = function () {
  if (canUndo()) {
    undo();
  }
};

playdate.leftButtonDown = function () {
  changePick(-1);
};

playdate.rightButtonDown = function () {
  changePick(1);
};

playdate.upButtonDown = function () {
  changePick(-3);
};

playdate.downButtonDown = function () {
  changePick(3);
};

let crankWasDocked = playdate.isCrankDocked();

playdate.update = function () {
  playdate.timer.updateTimers();
  playdate.graphics.sprite.update();
  if (crankWasDocked) {
    if (playdate.isCrankDocked()) {
      playdate.ui.crankIndicator.update();
    } else {
      crankWasDocked = false;
      gfxNeedUpdate(true, true);
      playdate.graphics.clear();
    }
  }
  gfxUpdate(game);
};

function initSettings() {
  const readSettings = playdate.datastore.read("settings");
  if (readSettings == null) {
    settings.level = 0;
  } else {
    settings = readSettings;
  }
}

function saveSettings() {
  playdate.datastore.write(settings, "settings");
}

function initMenus() {
  const menu = playdate.getSystemMenu();
  menu.addMenuItem("new game", function () {
    startNewGame();
  });
  menu.addOptionsMenuItem("level", DIFFICULTY_LEVELS, DIFFICULTY_LEVELS[settings.level], function (chosen) {
    const oldLevel = settings.level;
    let newLevel = oldLevel;
    for (let i = 0; i < DIFFICULTY_LEVELS.length; i++) {
      if (chosen === DIFFICULTY_LEVELS[i]) {
        newLevel = i;
        break;
      }
    }
    if (oldLevel !== newLevel) {
      settings.level = newLevel;
      saveSettings();
      startNewGame();
    }
  });
}

function loadState() {
  const state = playdate.datastore.read("game");
  if (state == null) {
    return false;
  }
  Object.assign(game, state.game);
  undoStack = state.undoStack || [];
  return true;
}

function saveState() {
  if (levelIsComplete()) {
    playdate.datastore.delete("game");
    return;
  }

  playdate.datastore.write({
    game,
    undoStack
  }, "game");
}

playdate.gameWillTerminate = saveState;
playdate.deviceWillSleep = saveState;

function initSounds() {
  sounds.start = playdate.sound.sampleplayer.new("snd/start");
  sounds.click = playdate.sound.sampleplayer.new("snd/click");
  sounds.change = playdate.sound.sampleplayer.new("snd/change");
  sounds.slot = playdate.sound.sampleplayer.new("snd/slot");
  sounds.unslot = playdate.sound.sampleplayer.new("snd/unslot");
  sounds.unlock = playdate.sound.sampleplayer.new("snd/unlock");
}

function initGame() {
  // initialize stuff
  initSettings();
  initMenus();
  initSounds();

  // show crank indicator if it's docked
  if (crankWasDocked) {
    playdate.ui.crankIndicator.start();
  }

  // load or start the game
  if (!loadState()) {
    startNewGame();
  }
}

initGame();

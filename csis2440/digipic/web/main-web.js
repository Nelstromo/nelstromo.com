// Web harness for Playdate-ported game
// Emulates a subset of the Playdate API and wires keyboard controls

import { createLevel, fitTest, offsetPick, bitsToValue } from "../Source/game.js";
import { animateRingChange, gfxNeedUpdate, startLevelGfx, animateRotation, gfxUpdate } from "../Source/gfx.js";
import "./playdate-shim.js";

// Create canvas and attach to shim
const root = document.getElementById("root");
const canvas = document.createElement("canvas");
canvas.width = 400; // view scale
canvas.height = 400;
root.appendChild(canvas);

// Wire the shim to draw to our canvas
window.__pd_setCanvas?.(canvas);

// Import the game logic that references global `playdate` and starts itself
await import("../Source/main.js");

// Controls overlay is defined in HTML

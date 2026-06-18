const allowedImages = new Set([
    "pawn-w.png", "pawn-b.png",
    "rook-w.png", "rook-b.png",
    "knight-w.png", "knight-b.png",
    "bishop-w.png", "bishop-b.png",
    "queen-w.png", "queen-b.png",
    "king-w.png", "king-b.png"
]);

const pieces = [];

class Piece {
    #color;
    #name;
    #position;
    #image;

    constructor(color, name, position, image) {
        if (color === "light" || color === "dark") {
            this.#color = color;
        } else {
            console.error(`Invalid Color "${color}". Must be "light" or "dark"`);
        }

        if (typeof name === "string" && name.trim().length > 0) {
            this.#name = name;
        } else {
            console.error(`Invalid Name "${name}". Must be a non-empty string.`);
        }

        if (allowedImages.has(image)) {
            this.#image = image;
        } else {
            console.error(`Invalid image "${image}". Must be an allowed file name.`);
        }

        if (/^[a-h][1-8]$/i.test(position)) {
            this.#position = position.toLowerCase();
        } else {
            console.error(`Invalid position "${position}". Must be in the form of a1-h8.`);
        }
    }

    // Getters and Setters
    get color() {
        return this.#color;
    }

    set color(value) {
        if (value === "light" || value === "dark") {
            this.#color = value;
        } else {
            console.error(`Invalid Color "${value}". Must be "light" or "dark".`);
        }
    }

    get name() {
        return this.#name;
    }

    set name(value) {
        if (typeof value === "string" && value.trim().length > 0) {
            this.#name = value.trim();
        } else {
            console.error(`Invalid Name "${value}". Must be a non-empty string.`);
        }
    }

    get position() {
        return this.#position;
    }

    set position(value) {
        if (/^[a-h][1-8]$/i.test(value)) {
            this.#position = value.toLowerCase();
        } else {
            console.error(`Invalid position "${value}". Must be in the form of a1-h8.`);
        }
    }

    get image() {
        return this.#image;
    }

    set image(value) {
        if (allowedImages.has(value)) {
            this.#image = value;
        } else {
            console.error(`Invalid image "${value}". Must be an allowed file name.`);
        }
    }

    toString() {
        return `${this.name.toUpperCase()} at ${this.position.toUpperCase()} using ${this.image}`;
    }
}

// setting up the bpard
function getImageName(type, color) {
    const colorCode = (color === "light") ? "w" : "b";
    return `${type}-${colorCode}.png`;
}

const files = ["a", "b", "c", "d", "e", "f", "g", "h"];
const backRow = ["rook", "knight", "bishop", "queen", "king", "bishop", "knight", "rook"];

function setBoard() {
    // Create pawns
    for (let i = 0; i < 8; i++) {
        pieces.push(new Piece("light", `WHITE ${files[i].toUpperCase()} PAWN`, `${files[i]}2`, getImageName("pawn", "light")));
        pieces.push(new Piece("dark", `BLACK ${files[i].toUpperCase()} PAWN`, `${files[i]}7`, getImageName("pawn", "dark")));
    }

    // Create back rows
    for (let i = 0; i < 8; i++) {
        pieces.push(new Piece("light", `WHITE ${backRow[i].toUpperCase()}`, `${files[i]}1`, getImageName(backRow[i], "light")));
        pieces.push(new Piece("dark", `BLACK ${backRow[i].toUpperCase()}`, `${files[i]}8`, getImageName(backRow[i], "dark")));
    }

    // put pieces on the board
    pieces.forEach(piece => {
        const square = document.getElementById(piece.position);
        const img = document.createElement("img");
        img.src = `img/${piece.image}`;
        img.alt = piece.name;
        square.appendChild(img);
    });

    document.getElementById("setBoardButton").disabled = true;

    console.log(pieces);
}

document.getElementById("setBoardButton").addEventListener("click", setBoard);

// Flip board Logic
const outerBoard = document.getElementById('outerBoard');
const rotateButton = document.getElementById('rotateButton');

rotateButton.addEventListener('click', () => {
    outerBoard.classList.toggle('flipped');
});

// changing colors for board buttons and Logic
const themeClasses = ["theme-1", "theme-2", "theme-3", "theme-4"];
let themeIndex = 0;
const changeBoardTheme = document.getElementById('changeBoardTheme');

changeBoardTheme.addEventListener('click', () => {
    outerBoard.classList.remove(...themeClasses); // Remove all old themes
    themeIndex = (themeIndex + 1) % themeClasses.length;
    outerBoard.classList.add(themeClasses[themeIndex]);
});
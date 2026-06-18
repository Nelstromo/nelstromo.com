const robot = document.getElementById('robot');
const msg = document.getElementById("instructions");
const closeBtn = document.getElementById("closeInstructions");
let controlsEnabled = false;

let row = 1;
let column = 2;

const grid = [
    ["topZero", "topOne", "topTwo", "topThree", "topFour"],
    ["middleZero", "middleOne", "middleTwo", "middleThree", "middleFour"],
    ["bottomZero", "bottomOne", "bottomTwo", "bottomThree", "bottomFour"]
];

const isOpen = [
    [true, true, true, true, true],
    [true, true, true, true, true],
    [true, true, true, true, true]
];

function moveTo(newRow, newColumn) {
    const escapingTop = (row === 0 && column === 2 && newRow === -1);
    const escapingLeft = (row === 1 && column === 0 && newColumn === -1);
    const escapingRight = (row === 1 && column === 4 && newColumn === 5);
    const escapingBottom = (row === 2 && column === 2 && newRow === 3);

    const exiting = escapingTop || escapingLeft || escapingRight || escapingBottom;

    if (exiting) {
        robot.classList.add("escaped");
        robot.style.position = "absolute";

        const box = document.querySelector(".box");

        if (escapingTop) {
            robot.style.top = "-90px";
            robot.style.left = "262px";
        } else if (escapingBottom) {
            robot.style.top = "390px";
            robot.style.left = "262px";
        } else if (escapingLeft) {
            robot.style.top = "137px";
            robot.style.left = "-90px";
        } else if (escapingRight) {
            robot.style.top = "137px";
            robot.style.left = "640px";
        }

        box.appendChild(robot);

        setTimeout(() => {
            document.getElementById("gameEnd").style.display = "flex";
            document.removeEventListener("keydown", handleKeydown);
        }, 500);
        return;
    }


    if (
        newRow >= 0 && newRow <= 2 &&
        newColumn >= 0 && newColumn <= 4 &&
        isOpen[newRow][newColumn]
    ) {
        row = newRow;
        column = newColumn;
        const newCell = document.querySelector(`.${grid[row][column]}`);
        newCell.appendChild(robot);
        robot.classList.remove("escaped");
        robot.style = "";
    }
}

function handleKeydown(event) {
    if (!controlsEnabled) return;

    switch (event.key) {
        case "ArrowUp":
            moveTo(row - 1, column);
            break;
        case "ArrowDown":
            moveTo(row + 1, column);
            break;
        case "ArrowLeft":
            moveTo(row, column - 1);
            break;
        case "ArrowRight":
            moveTo(row, column + 1);
            break;
    }
}

document.addEventListener("keydown", handleKeydown);

closeBtn.addEventListener("click", () => {
    msg.style.display = "none";
    controlsEnabled = true;
});

function showResetButton() {
    document.getElementById("gameEnd").style.display = "flex";
}
document.getElementById("playAgain").addEventListener("click", () => {
    row = 1;
    column = 2;
    const middleTwo = document.querySelector(".middleTwo");
    robot.classList.remove("escaped");
    robot.style = "";
    middleTwo.appendChild(robot);

    document.getElementById("gameEnd").style.display = "none";
    document.addEventListener("keydown", handleKeydown);
    controlsEnabled = true;
});

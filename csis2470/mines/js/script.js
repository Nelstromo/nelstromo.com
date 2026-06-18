let brush, world, ship, home, stars, mines;
gameOver = false;

let explosionImage = new Image();
explosionImage.src = 'img/explosion.png';


explosionImage.onload = () => {
    explosionReady = true;
};

let explosionSound = new Audio('audio/explosion.mp3');

let showExplosion = false; 
let explosionX = 0;
let explosionY = 0;
let explosionTriggered = false; 

window.addEventListener('resize', setUpWorld);
setUpWorld();


function setUpWorld() {
    window.addEventListener('keydown', navigate);


    world = document.querySelector("canvas");
    world.width = window.innerWidth;
    world.height = window.innerHeight;
    brush = world.getContext('2d');

    ship = {
        x: 0,
        y: 0,
        sizeHeight: 90,
        sizeWidth: 87,
        speedX: 50,
        speedY: 50,
        model: new Image()
    };

    ship.model.src = "img/ship.png";
    ship.x = (world.width - ship.sizeWidth) / 2;
    ship.y = world.height - ship.sizeHeight - 12;

    home = {
        w: 300,
        h: 200,
        x: Math.random() * (world.width - 300),
        y: Math.random() * (world.height - 200),
    };

    allStars(150);
    allMines(20);

    ship.model.onload = () => {
        draw();
    };
}
function draw() {

    if (isHome() && !gameOver) {
        gameOver = true;
        mines = []; 
        winGame();
    }

    brush.clearRect(0, 0, world.width, world.height);

    stars.forEach(star => star.show());

    if (!gameOver && mines.length > 0) {
        mines.forEach(mine => mine.show());
    }

    buildShip();
    makeHome();

    if (isDead() && !explosionTriggered) {
        explosionTriggered = true;
        showExplosion = true;

        explosionX = ship.x + ship.sizeWidth / 2 - 75;
        explosionY = ship.y + ship.sizeHeight / 2 - 75;

        explosionSound.currentTime = 0;
        explosionSound.play();

        loseGame();
    }

    if (showExplosion && explosionReady) {
        brush.drawImage(explosionImage, explosionX, explosionY, 150, 150);

        const loseMessage = 'Your Ship Got Blown Up :(';
        const restartLoseMessage = 'Press ESC to Try Again!';

        brush.fillStyle = "rgba(0, 0, 0, 0.6)";
        brush.fillRect(world.width / 2 - 300, world.height / 2 - 100, 600, 150);

        brush.fillStyle = "#f83825";
        brush.font = 'bold 30px Arial';
        brush.textAlign = 'center';
        brush.fillText(loseMessage, world.width / 2, world.height / 2);
        brush.fillText(restartLoseMessage, world.width / 2, world.height / 2 + 40);
    }

    if (gameOver && isHome()) {
        brush.fillStyle = "rgba(0, 0, 0, 0.6)";
        brush.fillRect(world.width / 2 - 300, world.height / 2 - 100, 600, 150);

        const winMessage = 'You Completed Your Mission!';
        const restartWinMessage = "Press ESC to Play Again!";
        brush.fillStyle = "#f83825";
        brush.font = 'bold 30px Arial';
        brush.textAlign = 'center';
        brush.fillText(winMessage, world.width / 2, world.height / 2);
        brush.fillText(restartWinMessage, world.width / 2, world.height / 2 + 40);
    }

    if (!gameOver) {
        requestAnimationFrame(draw);
    }
}

function buildShip() {
    brush.drawImage(
        ship.model,
        ship.x,
        ship.y,
        ship.sizeWidth,
        ship.sizeHeight
    );
}

function navigate(e) {
    switch (e.code) {
        case 'ArrowUp':
            if (ship.y - ship.speedY >= 0) ship.y -= ship.speedY;
            break;
        case 'ArrowDown':
            if (ship.y + ship.sizeHeight + ship.speedY <= world.height) ship.y += ship.speedY;
            break;
        case 'ArrowLeft':
            if (ship.x - ship.speedX >= 0) ship.x -= ship.speedX;
            break;
        case 'ArrowRight':
            if (ship.x + ship.sizeWidth + ship.speedX <= world.width) ship.x += ship.speedX;
            break;
    }
}

function makeHome() {
    brush.beginPath();
    brush.strokeStyle = 'white';
    brush.lineWidth = 8;
    brush.rect(home.x, home.y, home.w, home.h);
    brush.stroke();
    brush.closePath();
}

function isHome() {
    return (
        ship.x >= home.x &&
        ship.x + ship.sizeWidth <= home.x + home.w &&
        ship.y >= home.y &&
        ship.y + ship.sizeHeight <= home.y + home.h
    );
}


function winGame() {
    window.removeEventListener('keydown', navigate);
    window.addEventListener('keydown', replay);
}

function loseGame() {
    mines = [];
    window.removeEventListener('keydown', navigate);
    window.addEventListener('keydown', replay);
}

function replay(e) {
    if (e.code === 'Escape') {
        showExplosion = false;
        explosionTriggered = false;
        gameOver = false;

        window.removeEventListener('keydown', replay);

        setUpWorld();
    }
}

function makeStars() {
    this.x = Math.random() * world.width;
    this.y = Math.random() * world.height;
    this.r = Math.random() * 2 + 1;

    this.show = function () {
        brush.beginPath();
        brush.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        brush.fillStyle = 'white';
        brush.fill();
        brush.closePath();
    };
}

function allStars(count) {
    stars = [];
    for (let i = 0; i < count; i++) {
        stars.push(new makeStars());
    }
}

function makeMines() {
    this.w = 50;
    this.h = 50;
    this.x = Math.random() * (world.width - this.w);
    this.y = Math.random() * (world.height - this.h);

    this.show = function () {
        brush.fillStyle = 'white';
        brush.fillRect(this.x, this.y, this.w, this.h);
    };
}

function allMines(count) {
    mines = [];
    const buffer = 25; 
    const minDistanceFromShip = 100; 

    while (mines.length < count) {
        let mine = new makeMines();

        const bufferHome = {
            x: home.x - buffer,
            y: home.y - buffer,
            w: home.w + buffer * 2,
            h: home.h + buffer * 2
        };

        // no mines in home area
        const noMinesInHome =
            mine.x < bufferHome.x + bufferHome.w &&
            mine.x + mine.w > bufferHome.x &&
            mine.y < bufferHome.y + bufferHome.h &&
            mine.y + mine.h > bufferHome.y;

        const shipCenterX = ship.x + ship.sizeWidth / 2;
        const shipCenterY = ship.y + ship.sizeHeight / 2;
        const mineCenterX = mine.x + mine.w / 2;
        const mineCenterY = mine.y + mine.h / 2;

        const dx = shipCenterX - mineCenterX;
        const dy = shipCenterY - mineCenterY;
        const distance = Math.sqrt(dx * dx + dy * dy);

        const tooCloseToShip = distance < minDistanceFromShip;

        if (!noMinesInHome && !tooCloseToShip) {
            mines.push(mine);
        }
    }
}


function isDead() {
    for (let mine of mines) {
        if (
            ship.x < mine.x + mine.w &&
            ship.x + ship.sizeWidth > mine.x &&
            ship.y < mine.y + mine.h &&
            ship.y + ship.sizeHeight > mine.y
        ) {
            return true;
        }
    }
    return false;
}

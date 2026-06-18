let hasRolled = false;

document.getElementById('rollDice').addEventListener('click', () => {
    const roll1 = Math.floor(Math.random() * 6) + 1;
    const roll2 = Math.floor(Math.random() * 6) + 1;
    const total = roll1 + roll2;

    if (!hasRolled) {
        document.getElementById('rollDice').textContent = 'Roll Again';
        hasRolled = true;

        const gif = document.getElementById('gif');
        gif.classList.add('animate-slide');

        gif.addEventListener('animationend', () => {
            gif.remove();
        });
    }

    const die1 = document.getElementById('dice1');
    const die2 = document.getElementById('dice2');

    createDieSide(die1, roll1);
    createDieSide(die2, roll2);

    const resultMsg = document.getElementById('resultMsg');
    const diceMsg = document.getElementById('diceMsg');
    const doublesMsg = document.getElementById('doublesMsg');

    resultMsg.textContent = `Great job! You rolled a ${total}`;

    // Messages!
    if (total < 6) {
        diceMsg.textContent = "Maybe you should invest in some better dice.";
    } else if (total > 7) {
        diceMsg.textContent = "Outstanding Roll! The Stars really are alligned for you aren't they!";
    } else if (total == 7) {
        diceMsg.textContent = "Seven! Tons of Gold for you! Tons of gold for me! Tons of gold for we!";
    } else {
        diceMsg.textContent = "6! Statistically average, just like your pile of gold";
    }

    // Double?
    if (roll1 === roll2) {
        doublesMsg.textContent = `You got two ${roll1}s! You aren't by chance playing with Loaded Dice are you?`;
    } else {
        doublesMsg.textContent = '';
    }
});

function getDotsForNumber(num) {
    const dotMap = {
        1: ['dot1'],
        2: ['dot2', 'dot5'],
        3: ['dot2', 'dot1', 'dot5'],
        4: ['dot2', 'dot3', 'dot4', 'dot5'],
        5: ['dot2', 'dot3', 'dot1', 'dot4', 'dot5'],
        6: ['dot2', 'dot3', 'dot6', 'dot7', 'dot4', 'dot5']
    };
    return dotMap[num];
}

function createDieSide(div, num) {
    div.innerHTML = '';
    div.className = '';
    div.classList.add(['zero', 'one', 'two', 'three', 'four', 'five', 'six'][num]);
    div.classList.remove('zero');

    const dots = getDotsForNumber(num);
    dots.forEach(dotClass => {
        const dot = document.createElement('div');
        dot.classList.add('die-dot', dotClass);
        div.appendChild(dot);
    });
}


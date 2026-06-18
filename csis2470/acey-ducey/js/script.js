let hasRolled = false;
let userBank = 100;
let lowCardIndex, highCardIndex; 
let lowLimit = 0, highLimit = 0; 


const card1 = document.getElementById("card1");
const card2 = document.getElementById("card2");
const card3 = document.getElementById("card3");
const cardValue = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
const betInput = document.getElementById("bet");
const placeBetButton = document.getElementById("place-bet");
const bankDisplay = document.getElementById("userBank");
const playAgainBtn = document.getElementById("play-again");
const resetBtn = document.getElementById("reset");
const adminPass = document.getElementById("cheat-mode");
const helpLink = document.getElementById("help-link");
const aiLink = document.getElementById("ai-link");
const popup = document.getElementById("popup");
const popMsg = document.getElementById("popupmsg");
const msg = document.getElementById("msg");

const noRiskNoGainMsgs = [
    "What's this? A ghost bet? Even Monopoly money has more weight than that.",
    "Trying to save your cash? This isn't a piggy bank simulator, champ.",
    "Zero? That's bold… bold like ordering water at a bar.",
    "You can't win big if you don't bet at all—unless you're playing Uno.",
    "Oh come on, put something down! Even pocket lint counts more than that."
];

const tooHighMsgs = [
    "Ah yes, the ol' imaginary credit line—approved nowhere.",
    "Betting more than you own? Even Vegas would laugh you out the door.",
    "You've got champagne dreams on a soda budget.",
    "This isn't a loan office—try again with money you actually have.",
    "Nice try, Rockefeller. Check your balance before flexing."
];

const winMsgs = [
    "*Slow clap*  fine, you got this one.",
    "Ugh, even a broken clock is right twice a day.",
    "Enjoy it--luck won't babysit you forever.",
    "Okay, okay you win. Don't let it go to your head.",
    "Congrats, I guess. The cards must like you better than I do."
];

const lostMsgs = [
    "Ooooh, the house thanks you for your generous donation.",
    "And just like that- your money went *poof*.",
    "Luck called in sick today. Maybe tomorrow.",
    "That bet aged like milk. Sour and gone quick.",
    "Don't worry, losing builds character… and empties wallets."
];


function drawCards() {
    lowLimit = 0;
    highLimit = 0;

    if (adminPass.checked) {
        lowLimit = 0;  
        highLimit = 12; 
    } else {

        while (lowLimit === highLimit) {
            lowLimit = Math.floor(Math.random() * cardValue.length);
            highLimit = Math.floor(Math.random() * cardValue.length);
        }
    }


    lowCardIndex = Math.min(lowLimit, highLimit);
    highCardIndex = Math.max(lowLimit, highLimit);

    card1.textContent = cardValue[lowCardIndex];
    card2.textContent = cardValue[highCardIndex];
    card3.classList.add("hidden");

    msg.textContent = "";
    betInput.value = "";
    betInput.classList.remove("hidden");
    placeBetButton.classList.remove("hidden");
    playAgainBtn.classList.add("hidden");
}

function placeBet() {
    let bet = parseInt(betInput.value);

    if (isNaN(bet) || bet <= 0) {
        msg.textContent = showRandomMessage(noRiskNoGainMsgs);
        return;
    }

    if (bet > userBank) {
        msg.textContent = showRandomMessage(tooHighMsgs);
        return;
    }

    let thirdIndex;
    if (adminPass && adminPass.checked) {

        thirdIndex = 2;
    } else {
        thirdIndex = Math.floor(Math.random() * cardValue.length);
    }

    const thirdValue = getCardValue(thirdIndex);
    const lowValue = getCardValue(lowCardIndex);
    const highValue = getCardValue(highCardIndex);

    card3.textContent = cardValue[thirdIndex];
    card3.classList.remove("hidden");

    if (thirdValue > lowValue && thirdValue < highValue) {
        userBank += bet;
        msg.textContent = `You won $${bet}! ${showRandomMessage(winMsgs)}`;
    } else {
        userBank -= bet;
        msg.textContent = `You lost $${bet}. ${showRandomMessage(lostMsgs)}`;
    }

    updateBank();

    betInput.classList.add("hidden");
    placeBetButton.classList.add("hidden");

    if (userBank >= 500) {
        msg.textContent += " You won, are you proud?!";
        resetBtn.classList.remove("hidden");
    } else if (userBank <= 0) {
        msg.textContent += " Game over man! Game over!";
        resetBtn.classList.remove("hidden");
    } else {
        playAgainBtn.classList.remove("hidden");
    }
}


function showRandomMessage(array) {
    const index = Math.floor(Math.random() * array.length);
    return array[index];
}

function updateBank() {
    bankDisplay.textContent = `Bank: $${userBank}`;
}

helpLink.addEventListener("click", (e) => {
    e.preventDefault();

    popMsg.innerHTML = `
    <h2>Quick Rules</h2>
  <ul>
    <li>Two cards are dealt face up (lowest first, highest second).</li>
    <li>You place a bet (must be greater than 0 and within your bank).</li>
    <li>A third card is drawn:</li>
    <ul>
      <li>If its between the first two →  You win your bet!</li>
      <li>If its outside or equal →  You lose your bet.</li>
    </ul>
    <li>Game ends when your bank hits $0 (busted) or youve won big.</li>
  </ul>
  `;
    popup.classList.remove("hidden");
});

aiLink.addEventListener("click", (e) => {
    e.preventDefault();
    popMsg.innerHTML = `
    <h3>AI Prompt Credits</h3>
    <p><strong>Win Messages Prompt:</strong> "Give me 5 begrudging applause for the user winning a hand."<br><em>Source: ChatGPT</em></p>
    <p><strong>Lose Violation Prompt:</strong> "Give me 5 sassy messages for when the player loses their hand."<br><em>Source: ChatGPT</em></p>
    <p><strong>Mocking Messages Prompt:</strong> "Give me 5 light mocking messages for when the player bets nothing."<br><em>Source: ChatGPT</em></p>
    <p><strong>Budget Violation Prompt:</strong> "Give me 5 snarky messages for when they bet too much."<br><em>Source: ChatGPT</em></p>
    <p><strong>Game Instructions Prompt:</strong> "Give me instructions on how to play acey ducey"<br><em>Source: ChatGPT</em></p>
  `;
    popup.classList.remove("hidden");
});

function closePopup() {
    popup.classList.add("hidden");
}

function getCardValue(index) {
    if (cardValue[index] === "A") return 1;
    if (cardValue[index] === "J") return 11;
    if (cardValue[index] === "Q") return 12;
    if (cardValue[index] === "K") return 13;
    return parseInt(cardValue[index]);
}

placeBetButton.addEventListener("click", placeBet);
playAgainBtn.addEventListener("click", drawCards);
resetBtn.addEventListener("click", () => location.reload());

drawCards();

[card1, card2].forEach(c => {
    c.classList.remove('revealed');
    requestAnimationFrame(() => c.classList.add('revealed'));
});

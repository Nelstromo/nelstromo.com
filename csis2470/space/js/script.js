const submit = document.querySelector('button[type=submit]');
const form = document.querySelector("form");

let validFirst = false;
let validLast = false;
let validRobot = false;
let validShip = false;
let validPlanet = false;

const firstName = document.getElementById("firstName");
const lastName = document.getElementById("lastName");
const robotName = document.getElementById("robotName");
const spaceShipName = document.getElementById("spaceShipName");
const planetName = document.getElementById("planetName");

const messageFirstName = document.querySelector("#firstName + small");
const messageLastName = document.querySelector("#lastName + small");
const messageRobot = document.querySelector("#robotName + small");
const messageShip = document.querySelector("#spaceShipName + small");
const messagePlanet = document.querySelector("#planetName + small");

const forbiddenRobotNames = ['bb-8', 'c-3po', 'r2-d2'];
const forbiddenShipNames = ['hunter iv', 'icarus i', 'icarus ii', 'zero x', 'terra v'];
const forbiddenPlanetNames = ['earth', 'mars', 'venus', 'saturn', 'neptune', 'uranus', 'mercury', 'jupiter', 'pluto'];

const patternName = /^(?=.*[XxZz])[A-Za-z]{5,}$/;
const patternRobot = /^[A-Z]\d-[A-Z]\d$|^[A-Z]{2}-\d$|^[A-Z]-\d[A-Z]{2}$/i;
const patternShip = /^[A-Za-z]{4,} (I|II|III|IV|V|VI|VII|VIII|IX|X)$/i;
const patternPlanet = /^[A-Za-z]{5,}$/;

firstName.addEventListener('input', () => {
    validateField(firstName, patternName, messageFirstName, "Name MUST be at least 5 letters and include ONE X or Z!", () => validFirst = true, () => validFirst = false);
    checkAllValid();
});

lastName.addEventListener('input', () => {
    validateField(lastName, patternName, messageLastName, "Name MUST be at least 5 letters and include ONE X or Z!", () => validLast = true, () => validLast = false);
    checkAllValid();
});

robotName.addEventListener('input', () => {
    const input = robotName.value.trim();
    const check = input.toLowerCase();

    if (!patternRobot.test(input)) {
        messageRobot.textContent = "Robot Name MUST follow patterns: LN-LN, LL-N, or L-NLL";
        messageRobot.style.color = "pink";
        validRobot = false;
    } else if (forbiddenRobotNames.includes(check)) {
        messageRobot.textContent = `Sorry! \"${input}\" is already taken!`;
        messageRobot.style.color = "pink";
        validRobot = false;
    } else {
        messageRobot.textContent = "Valid";
        messageRobot.style.color = "lightgreen";
        validRobot = true;
    }
    checkAllValid();
});

spaceShipName.addEventListener('input', () => {
    const input = spaceShipName.value.trim();
    const check = input.toLowerCase();

    if (!patternShip.test(input)) {
        messageShip.textContent = "Must have 4+ letters, space, and valid Roman numeral I-X";
        messageShip.style.color = "pink";
        validShip = false;
    } else if (forbiddenShipNames.includes(check)) {
        messageShip.textContent = `Sorry! \"${input}\" is already taken!`;
        messageShip.style.color = "pink";
        validShip = false;
    } else {
        messageShip.textContent = "Valid";
        messageShip.style.color = "lightgreen";
        validShip = true;
    }
    checkAllValid();
});

planetName.addEventListener('input', () => {
    const input = planetName.value.trim();
    const check = input.toLowerCase();

    if (!patternPlanet.test(input)) {
        messagePlanet.textContent = "Planet Name MUST be 5+ letters and one word!";
        messagePlanet.style.color = "pink";
        validPlanet = false;
    } else if (forbiddenPlanetNames.includes(check)) {
        messagePlanet.textContent = `\"${input}\" was destroyed 71 years ago! Select another planet. #neverforget`;
        messagePlanet.style.color = "pink";
        validPlanet = false;
    } else {
        messagePlanet.textContent = "Valid";
        messagePlanet.style.color = "lightgreen";
        validPlanet = true;
    }
    checkAllValid();
});

function validateField(field, pattern, messageBox, errorMsg, onValid, onInvalid) {
    const input = field.value.trim();
    if (!pattern.test(input)) {
        messageBox.textContent = errorMsg;
        messageBox.style.color = "pink";
        onInvalid();
    } else {
        messageBox.textContent = "Valid";
        messageBox.style.color = "lightgreen";
        onValid();
    }
}

function checkAllValid() {
    submit.disabled = !(validFirst && validLast && validRobot && validShip && validPlanet);
}

submit.addEventListener('click', processData);

function processData(e) {
    e.preventDefault();

    const output = document.getElementById("output");
    const main = document.getElementById("main");
    if (main) main.remove();

    const first = firstName.value.trim();
    const last = lastName.value.trim();
    const robot = robotName.value.trim();
    const ship = spaceShipName.value.trim();
    const planet = planetName.value.trim();

    output.innerHTML = `
    <div class="submission-result">
      <h1>Application Received!</h1>
      <p><strong>Commander:</strong> ${first} ${last}</p>
      <p><strong>Robot:</strong> ${robot}</p>
      <p><strong>Spaceship:</strong> ${ship}</p>
      <p><strong>Destination:</strong> ${planet}</p>
      <br>
      <p> You should hear back from us within 47 to 864 solar cycles. </p>
      <p> Thank you! </p>
    </div>
  `;
}

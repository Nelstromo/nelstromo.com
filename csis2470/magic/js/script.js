let deckID = null;
const flipDeckButton = document.getElementById('flipDeck');
const shuffleDeckButton = document.getElementById('shuffleDeck');
const cardLayout = document.getElementById('cardLayout');

shuffleDeckButton.disabled = true;
shuffleDeckButton.style.backgroundColor = 'grey';
shuffleDeckButton.style.cursor = 'not-allowed';

// Fetch new deck on page load
function getNewDeck() {
	const x = new XMLHttpRequest();
	x.open('GET', 'https://www.deckofcardsapi.com/api/deck/new/shuffle/?deck_count=1');
	x.send();

	x.addEventListener('readystatechange', () => {
		if (x.readyState === 4 && x.status === 200) {
			const data = JSON.parse(x.responseText);
			deckID = data.deck_id;
			cardSetup();
		}
	});
}

// Show card backs
function cardSetup() {
	cardLayout.innerHTML = '';

	for (let i = 0; i < 52; i++) {
		const img = document.createElement('img');
		img.src = 'img/back.png';
		img.alt = 'Card Back';
		img.className = 'card-back';
		img.id = `card-${i + 1}`;
		img.toggled = false;
		img.faceUp = false;
		img.style.zIndex = i;

		img.addEventListener('click', () => {
			if (!img.faceUp) {
				img.toggled = !img.toggled;
				img.style.marginBottom = img.toggled ? '50px' : '0';
			}
		});

		img.addEventListener('dblclick', () => {
			if (img.faceUp || !img.toggled) return;

			const drawCard = new XMLHttpRequest();
			drawCard.open('GET', `https://www.deckofcardsapi.com/api/deck/${deckID}/draw/?count=1`);
			drawCard.send();

			drawCard.addEventListener('readystatechange', () => {
				if (drawCard.readyState === 4 && drawCard.status === 200) {
					const cardData = JSON.parse(drawCard.responseText);
					if (cardData.cards?.length > 0) {
						const card = cardData.cards[0];
						img.src = card.image;
						img.alt = `${card.value} of ${card.suit}`;
						img.faceUp = true;
						img.toggled = false;
					}
				}
			});
		});

		cardLayout.appendChild(img);
	}
}

// Flip deck and let Shuffle button work
function flipDeck() {
	flipDeckButton.disabled = true;
	flipDeckButton.style.backgroundColor = 'grey';
	flipDeckButton.style.cursor = 'not-allowed';

	const drawNew = new XMLHttpRequest();
	drawNew.open('GET', `https://www.deckofcardsapi.com/api/deck/${deckID}/draw/?count=52`);
	drawNew.send();

	drawNew.addEventListener('readystatechange', () => {
		if (drawNew.readyState === 4 && drawNew.status === 200) {
			const drawData = JSON.parse(drawNew.responseText);
			
			if (drawData.cards?.length > 0) {
				cardLayout.innerHTML = '';

				drawData.cards.forEach((card, i) => {
					const img = document.createElement('img');
					img.src = card.image;
					img.alt = `${card.value} of ${card.suit}`;
					img.className = 'card-back';
					img.id = `card-${i + 1}`;
					img.faceUp = true;
					img.toggled = false;
					img.style.zIndex = i;
					cardLayout.appendChild(img);
				});
				
				
				// Allow shiffle button to work
				shuffleDeckButton.disabled = false;
				shuffleDeckButton.style.backgroundColor = 'yellow';
				shuffleDeckButton.style.cursor = 'pointer';
			}
		}
	});
	console.log('deck Flipped');
}

function shuffleDeck() {
	if (!deckID) return;

	const shuffleRequest = new XMLHttpRequest();
	shuffleRequest.open('GET', `https://www.deckofcardsapi.com/api/deck/${deckID}/shuffle/`);
	shuffleRequest.send();

	shuffleRequest.addEventListener('readystatechange', () => {
		if (shuffleRequest.readyState === 4 && shuffleRequest.status === 200) {
			flipDeck();
		}
	});
}

// Buttons
flipDeckButton.addEventListener('click', flipDeck);
shuffleDeckButton.addEventListener('click', shuffleDeck);

window.addEventListener('DOMContentLoaded', getNewDeck);

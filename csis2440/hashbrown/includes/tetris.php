<!-- Tetris Background Animation (Syndrome Style) -->
<style>
  #tetris-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    grid-template-rows: repeat(20, 1fr);
    gap: 1px;
    background: #0d0d0d;
    overflow: hidden;
  }

  .cell {
    width: 100%;
    height: 100%;
    background-color: transparent;
    transition: background-color 0.2s;
  }

  .block {
    background-color: #00ffff74;
    box-shadow: 0 0 8px #00ffffaa;
  }
</style>

<div id="tetris-bg"></div>

<script>
  const COLS = 10;
  const ROWS = 20;
  const MAX_HEIGHT = 12;
  const grid = Array.from({ length: ROWS }, () => Array(COLS).fill(null));
  const container = document.getElementById('tetris-bg');

  for (let r = 0; r < ROWS; r++) {
    for (let c = 0; c < COLS; c++) {
      const cell = document.createElement('div');
      cell.classList.add('cell');
      cell.dataset.row = r;
      cell.dataset.col = c;
      container.appendChild(cell);
    }
  }

  function getCell(row, col) {
    return document.querySelector(`.cell[data-row="${row}"][data-col="${col}"]`);
  }

  const SHAPES = {
    I: [[1, 1, 1, 1]],
    O: [[1, 1], [1, 1]],
    T: [[0, 1, 0], [1, 1, 1]],
    S: [[0, 1, 1], [1, 1, 0]],
    Z: [[1, 1, 0], [0, 1, 1]],
    J: [[1, 0, 0], [1, 1, 1]],
    L: [[0, 0, 1], [1, 1, 1]],
  };

  function rotate(shape, times = 1) {
    let result = shape;
    for (let t = 0; t < times; t++) {
      result = result[0].map((_, i) => result.map(row => row[i]).reverse());
    }
    return result;
  }

  function getRandomShape() {
    const keys = Object.keys(SHAPES);
    const key = keys[Math.floor(Math.random() * keys.length)];
    const rotations = Math.floor(Math.random() * 4);
    return rotate(SHAPES[key], rotations);
  }

  function getColumnForShape(shape) {
    return Math.floor(Math.random() * (COLS - shape[0].length + 1));
  }

  function getCurrentHeight() {
    for (let r = 0; r < ROWS; r++) {
      if (grid[r].some(cell => cell)) {
        return ROWS - r;
      }
    }
    return 0;
  }

  function spawnTetrimino() {
    let shape = getRandomShape();
    let col = getColumnForShape(shape);

    if (getCurrentHeight() >= MAX_HEIGHT - 2) {
      shape = SHAPES.I;
      col = 0;
    }

    let row = 0;
    const piece = { shape, row, col };
    fallPiece(piece);
  }

  function fallPiece(piece) {
    const interval = setInterval(() => {
      if (!canMove(piece, 1, 0)) {
        merge(piece);
        clearRows();
        spawnTetrimino();
        clearInterval(interval);
        return;
      }
      piece.row++;
      renderGrid();
      drawPiece(piece);
    }, 100);
  }

  function canMove(piece, dr, dc) {
    const { shape, row, col } = piece;
    for (let r = 0; r < shape.length; r++) {
      for (let c = 0; c < shape[0].length; c++) {
        if (!shape[r][c]) continue;
        const nr = row + dr + r;
        const nc = col + dc + c;
        if (nr >= ROWS || nc < 0 || nc >= COLS || (grid[nr] && grid[nr][nc])) {
          return false;
        }
      }
    }
    return true;
  }

  function merge(piece) {
    const { shape, row, col } = piece;
    for (let r = 0; r < shape.length; r++) {
      for (let c = 0; c < shape[0].length; c++) {
        if (shape[r][c]) {
          grid[row + r][col + c] = true;
        }
      }
    }
    renderGrid();
  }

  function clearRows() {
    let rowsCleared = false;
    for (let r = ROWS - 1; r >= 0; r--) {
      if (grid[r].every(cell => cell)) {
        grid.splice(r, 1);
        grid.unshift(Array(COLS).fill(null));
        rowsCleared = true;
        r++;
      }
    }
    if (rowsCleared) renderGrid();
  }

  function renderGrid() {
    for (let r = 0; r < ROWS; r++) {
      for (let c = 0; c < COLS; c++) {
        const cell = getCell(r, c);
        if (grid[r][c]) {
          cell.classList.add('block');
        } else {
          cell.classList.remove('block');
        }
      }
    }
  }

  function drawPiece(piece) {
    renderGrid();
    const { shape, row, col } = piece;
    for (let r = 0; r < shape.length; r++) {
      for (let c = 0; c < shape[0].length; c++) {
        if (shape[r][c]) {
          const cell = getCell(row + r, col + c);
          if (cell) cell.classList.add('block');
        }
      }
    }
  }

  spawnTetrimino();
</script>

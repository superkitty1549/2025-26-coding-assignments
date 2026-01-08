let bird;
let pipes = [];
let bg1, bg2, bg3, bg4;
let planeSheet;
let pipeImg, laserImg;
let skyImg;
let gameOver = false;
let score = 0;
let gameStarted = false;

let flapSound, scoreSound, hitSound;

let bgX = 0;


function preload() {
  skyImg = loadImage('assets/sky.png');
  bg1 = loadImage('assets/rocks_1.png');
  bg2 = loadImage('assets/rocks_2.png');
  bg3 = loadImage('assets/clouds_1.png');
  bg4 = loadImage('assets/clouds_2.png');
  
  planeSheet = loadImage('assets/planes_sheet.png');
  
  pipeImg = loadImage('assets/pipe.png');
  laserImg = loadImage('assets/laser2.png');
  
  flapSound = loadSound('assets/bgm_menu.mp3');
  scoreSound = loadSound('assets/score.wav');
  hitSound = loadSound('assets/game_over.wav');
  
  gameFont = loadFont('assets/gemunu-libre-v8-latin-700.ttf');
}

function setup() {
  createCanvas(600, 800);
  bird = new Bird();
  textFont(gameFont);
  
  flapSound.loop();
  flapSound.setVolume(0.5);
}

function draw() {
  drawBackground();
  
  if (!gameStarted) {
    bird.show();
    fill(255);
    stroke(0);
    strokeWeight(4);
    textAlign(CENTER);
    textSize(48);
    text('TAPPY PLANE', width / 2, height / 3);
    textSize(24);
    text('Press SPACE to Start', width / 2, height / 2);
    noStroke();
    return;
  }
  
  if (!gameOver) {
    bird.update();
    
    if (frameCount % 90 === 0) {
      pipes.push(new Pipe());
    }
    
    for (let i = pipes.length - 1; i >= 0; i--) {
      pipes[i].update();
      pipes[i].show();
      
      if (pipes[i].hits(bird)) {
        gameOver = true;
        hitSound.play();
      }
      
      if (pipes[i].offscreen()) {
        pipes.splice(i, 1);
      } else if (!pipes[i].passed && pipes[i].x + pipes[i].w < bird.x) {
        pipes[i].passed = true;
        score++;
        scoreSound.play();
      }
    }
    
    if (bird.y >= height - bird.h || bird.y <= 0) {
      gameOver = true;
      hitSound.play();
    }
  }
  
  bird.show();
  
  fill(255);
  stroke(0);
  strokeWeight(4);
  textAlign(CENTER);
  textSize(48);
  text(score, width / 2, 80);
  noStroke();
  
  if (gameOver) {
    fill(0, 150);
    rect(0, 0, width, height);
    fill(255);
    stroke(0);
    strokeWeight(4);
    textSize(64);
    text('GAME OVER', width / 2, height / 2 - 50);
    textSize(32);
    text('Score: ' + score, width / 2, height / 2 + 20);
    textSize(24);
    text('Press SPACE to Restart', width / 2, height / 2 + 80);
    noStroke();
  }
}

function drawBackground() {
  bgX -= 1;
  if (bgX <= -width) {
    bgX = 0;
  }
  
  image(skyImg, 0, 0, width, height);
  
  image(bg4, bgX * 0.5, 0, width, height);
  image(bg4, bgX * 0.5 + width, 0, width, height);
  
  image(bg3, bgX * 0.8, 0, width, height);
  image(bg3, bgX * 0.8 + width, 0, width, height);
  
  // Draw rocks in layers (front rocks faster)
  image(bg2, bgX * 1.5, 0, width, height);
  image(bg2, bgX * 1.5 + width, 0, width, height);
  
  image(bg1, bgX * 2, 0, width, height);
  image(bg1, bgX * 2 + width, 0, width, height);
}

function keyPressed() {
  if (key === ' ') {
    handleInput();
  }
}

function mousePressed() {
  handleInput();
}

function handleInput() {
  if (!gameStarted) {
    gameStarted = true;
    return;
  }
  
  if (gameOver) {
    gameOver = false;
    score = 0;
    pipes = [];
    bird = new Bird();
    loop();
  } else {
    bird.flap();
    // flapSound.play();
  }
}

class Bird {
  constructor() {
    this.x = 80;
    this.y = height / 2;
    this.w = 60;
    this.h = 50;
    this.velocity = 0;
    this.gravity = 0.6;
    this.lift = -8;
    this.spriteIndex = 0;
    this.spriteTimer = 0;
  }
  
  flap() {
    this.velocity = this. lift;
  }
  
  update() {
    this.velocity += this.gravity;
    this.y += this.velocity;
    
    if (this.velocity > 10) {
      this.velocity = 10;
    }
    
    this.spriteTimer++;
    if (this.spriteTimer > 5) {
      this.spriteTimer = 0;
      this.spriteIndex = (this.spriteIndex + 1) % 3;
    }
  }
  
  show() {
    push();
    translate(this.x + this.w / 2, this.y + this.h / 2);
    
    imageMode(CENTER);
    
    let frameWidth = planeSheet.width / 3;
    let frameHeight = planeSheet.height;
    let sx = this.spriteIndex * frameWidth;
    
    image(planeSheet, 0, 0, this.w, this.h, sx, 0, frameWidth, frameHeight);
    pop();
  }
}


class Pipe {
  constructor() {
    this.spacing = 220; 
    this.top = random(100, height - this.spacing - 100);
    this.bottom = this.top + this.spacing;
    this.x = width;
    this.w = 80;
    this.speed = 3;
    this.passed = false;
  }
  
  update() {
    this.x -= this.speed;
  }
  
  show() {
    image(pipeImg, this.x, 0, this.w, this.top);
    
    image(pipeImg, this.x, this.bottom, this.w, height - this.bottom);
    
    let gapHeight = this.bottom - this.top;
    for (let i = 0; i < gapHeight; i += 20) {
      image(laserImg, this.x, this.top + i, this.w, 20);
    }
  }
  
  hits(bird) {
    if (bird.x + bird.w > this.x && bird.x < this.x + this.w) {
      if (bird.y < this.top || bird.y + bird.h > this.bottom) {
        return true;
      }
    }
    return false;
  }
  
  offscreen() {
    return this.x < -this.w;
  }
}
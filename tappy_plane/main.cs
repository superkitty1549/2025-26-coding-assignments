using Godot;
using System;

public partial class Main : Node2D
{
    [Export] public PackedScene ObstacleScene;
    
    private Timer _spawnTimer;
    private Label _scoreLabel;
    private CanvasLayer _ui;
    private TextureRect _gameOverTexture;
    private TextureRect _getReadyTexture;
    private TextureRect _tapButton;
    private ParallaxBackground _parallaxBg;
    
    private int _score = 0;
    private bool _gameOver = false;
    private bool _gameStarted = false;
    
    public override void _Ready()
    {
        // Setup spawn timer
        _spawnTimer = GetNode<Timer>("SpawnTimer");
        _spawnTimer.Timeout += OnSpawnTimerTimeout;
        _spawnTimer.WaitTime = 2.0f;
        
        // Setup UI
        _ui = GetNode<CanvasLayer>("UI");
        _scoreLabel = _ui.GetNode<Label>("ScoreLabel");
        _gameOverTexture = _ui.GetNode<TextureRect>("GameOver");
        _getReadyTexture = _ui.GetNode<TextureRect>("GetReady");
        _tapButton = _ui.GetNode<TextureRect>("TapButton");
        
        // Setup parallax background
        _parallaxBg = GetNode<ParallaxBackground>("ParallaxBackground");
        
        // Initial state - show get ready screen
        _gameOverTexture.Visible = false;
        _getReadyTexture.Visible = true;
        _tapButton.Visible = true;
        _scoreLabel.Visible = false;
    }
    
    public override void _Input(InputEvent @event)
    {
        if (!_gameStarted && !_gameOver && Input.IsActionJustPressed("flap"))
        {
            StartGame();
        }
    }
    
    private void StartGame()
    {
        _gameStarted = true;
        _getReadyTexture.Visible = false;
        _tapButton.Visible = false;
        _scoreLabel.Visible = true;
        _spawnTimer.Start();
        UpdateScore();
    }
    
    private void OnSpawnTimerTimeout()
    {
        if (_gameOver) return;
        
        var obstacle = ObstacleScene.Instantiate<Obstacle>();
        AddChild(obstacle);
        
        // Random Y position for gap
        var rand = new Random();
        float gapY = rand.Next(150, 450);
        obstacle.Position = new Vector2(1200, gapY);
    }
    
    public void AddScore()
    {
        _score++;
        UpdateScore();
    }
    
    private void UpdateScore()
    {
        _scoreLabel.Text = _score.ToString();
    }
    
    public void GameOver()
    {
        if (_gameOver) return;
        
        _gameOver = true;
        _spawnTimer.Stop();
        _gameOverTexture.Visible = true;
        
        GetTree().CallGroup("obstacles", "queue_free");
        
        // Restart after delay
        GetTree().CreateTimer(2.0).Timeout += () => GetTree().ReloadCurrentScene();
    }
}
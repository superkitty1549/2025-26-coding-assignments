using Godot;
using System;

public partial class Obstacle : Node2D
{
    [Export] public float ScrollSpeed = 200.0f;
    [Export] public float GapSize = 180.0f;
    
    private StaticBody2D _topRock;
    private StaticBody2D _bottomRock;
    private Area2D _scoreArea;
    private bool _scored = false;
    
    public override void _Ready()
    {
        AddToGroup("obstacles");
        
        // Create top rock (pointing down)
        _topRock = new StaticBody2D();
        var topSprite = new Sprite2D();
        var topCollision = new CollisionShape2D();
        var topRect = new RectangleShape2D();
        
        // Load rock down texture
        topSprite.Texture = GD.Load<Texture2D>("res://assets/rock_down.png");
        topRect.Size = new Vector2(80, 500);
        topCollision.Shape = topRect;
        topCollision.Position = new Vector2(0, -250);
        
        _topRock.AddChild(topSprite);
        _topRock.AddChild(topCollision);
        _topRock.Position = new Vector2(0, -GapSize / 2);
        _topRock.AddToGroup("obstacles");
        AddChild(_topRock);
        
        // Create bottom rock (pointing up)
        _bottomRock = new StaticBody2D();
        var bottomSprite = new Sprite2D();
        var bottomCollision = new CollisionShape2D();
        var bottomRect = new RectangleShape2D();
        
        // Load rock up texture
        bottomSprite.Texture = GD.Load<Texture2D>("res://assets/rock_up.png");
        bottomRect.Size = new Vector2(80, 500);
        bottomCollision.Shape = bottomRect;
        bottomCollision.Position = new Vector2(0, 250);
        
        _bottomRock.AddChild(bottomSprite);
        _bottomRock.AddChild(bottomCollision);
        _bottomRock.Position = new Vector2(0, GapSize / 2);
        _bottomRock.AddToGroup("obstacles");
        AddChild(_bottomRock);
        
        // Create score detection area
        _scoreArea = new Area2D();
        var scoreCollision = new CollisionShape2D();
        var scoreRect = new RectangleShape2D();
        scoreRect.Size = new Vector2(10, GapSize);
        scoreCollision.Shape = scoreRect;
        _scoreArea.AddChild(scoreCollision);
        _scoreArea.BodyEntered += OnScoreAreaBodyEntered;
        AddChild(_scoreArea);
    }
    
    public override void _Process(double delta)
    {
        Position -= new Vector2(ScrollSpeed * (float)delta, 0);
        
        // Remove when off screen
        if (Position.X < -200)
        {
            QueueFree();
        }
    }
    
    private void OnScoreAreaBodyEntered(Node2D body)
    {
        if (_scored) return;
        
        if (body is Player)
        {
            _scored = true;
            var main = GetParent<Main>();
            main.AddScore();
        }
    }
}
<?php
class GameService {
    private User $userModel;

    public function __construct(){
        $this->gameModel = new Game();
    }

    
}
<?php
class DevelopersController extends Controller{
    public function __construct(){
        $this -> layout = 'main';
    }

    # Function that takes user to the index
    public function index(){
        $this -> view('developers/index', []);
    }
}
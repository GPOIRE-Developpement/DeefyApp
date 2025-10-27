<?php

namespace iutnc\deefy\action;

class LogoutAction extends Action {
    public function execute(): string {
        session_start();
        session_unset();
        session_destroy();
        
        header('Location: ?action=default');
        exit();
    }
}

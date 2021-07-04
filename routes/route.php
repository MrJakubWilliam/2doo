<?php
session_start();
require_once "../middleware/auth.php";

require_once "../database/database.php";
require_once "../controllers/auth.php";
require_once "../controllers/category.php";
require_once "../controllers/list.php";
require_once "../controllers/listItem.php";
require_once "../controllers/household.php";
require_once "../controllers/chore.php";
require_once "../controllers/flatmate.php";
require_once "../controllers/joinRequest.php";


$controller = $_GET['controller'];
$resource = $_GET['resource'];
$data = [];

foreach ($_GET as $key => $parameter) {
    $data[$key] = $parameter;
}
unset($data['controller']);
unset($data['resource']);

if ($controller == "auth") {
    switch ($resource) {
        case 'authenticate':
            authCheck();
            AuthController::authenticate($data);
            break;

        case 'signup':
            authCheck();
            AuthController::signup($data);
            break;

        case 'logout':
            AuthController::logout($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "category") {
    authValidate();

    switch ($resource) {
        case 'index':
            CategoryController::index($data);
            break;

        case 'show':
            CategoryController::show($data);
            break;

        case 'destroy':
            CategoryController::destroy($data);
            break;

        case 'store':
            CategoryController::store($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "list") {
    authValidate();

    switch ($resource) {
        case 'index':
            ListController::index($data);
            break;

        case 'show':
            ListController::show($data);
            break;

        case 'destroy':
            ListController::destroy($data);
            break;

        case 'store':
            ListController::store($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "listitem") {
    authValidate();

    switch ($resource) {
        case 'destroy':
            ListItemController::destroy($data);
            break;

        case 'complete':
            ListItemController::complete($data);
            break;

        case 'store':
            ListItemController::store($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "household") {
    authValidate();

    switch ($resource) {
        case 'index':
            HouseholdController::index($data);
            break;

        case 'show':
            HouseholdController::show($data);
            break;

        case 'destroy':
            HouseholdController::destroy($data);
            break;

        case 'store':
            HouseholdController::store($data);
            break;

        case 'edit':
            HouseholdController::edit($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "joinrequest") {
    authValidate();

    switch ($resource) {
        case 'index':
            JoinRequestController::index($data);
            break;

        case 'destroy':
            JoinRequestController::destroy($data);
            break;

        case 'store':
            JoinRequestController::store($data);
            break;

        case 'accept':
            JoinRequestController::accept($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "chore") {
    authValidate();

    switch ($resource) {
        case 'index':
            ChoreController::index($data);
            break;

        case 'show':
            ChoreController::show($data);
            break;

        case 'destroy':
            ChoreController::destroy($data);
            break;

        case 'store':
            ChoreController::store($data);
            break;

        case 'update':
            ChoreController::update($data);
            break;

        case 'complete':
            ChoreController::complete($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} elseif ($controller == "flatmate") {
    authValidate();

    switch ($resource) {
        case 'index':
            FlatmateController::index($data);
            break;

        case 'destroy':
            FlatmateController::destroy($data);
            break;

        case 'updateprivilage':
            FlatmateController::updateprivilage($data);
            break;

        default:
            $data = ["status" => "error", "message" => "Bad resource!", "redir" => "back"];
            echo json_encode($data);
            break;
    }
} else {
    header("Location ../");
}

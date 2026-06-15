<?php
/**
 * Synology NAS Web Station용 데이터 제어 API (PHP)
 * 클라이언트 웹 브라우저에서 data.json을 조회하고 저장하는 역할을 합니다.
 */

// CORS 설정 (외부 브라우저나 다양한 장치에서 접근 가능하도록 허용)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS 요청 처리 (CORS Preflight 대응)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

$file = 'data.json';

// GET 요청: 데이터 파일 읽어오기
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if ($content !== false) {
            echo $content;
        } else {
            http_response_code(500);
            echo json_encode(["error" => "데이터 파일을 읽을 수 없습니다."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "데이터 파일이 존재하지 않습니다."]);
    }
} 
// POST 요청: 데이터 파일 덮어쓰기
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 요청 바디에서 JSON 데이터 파싱
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data === null) {
        http_response_code(400);
        echo json_encode(["error" => "유효하지 않은 JSON 데이터입니다."]);
        exit;
    }
    
    // JSON 직렬화 (들여쓰기 적용, 한글 깨짐 방지)
    $jsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // 파일 쓰기 수행
    if (file_put_contents($file, $jsonString) !== false) {
        echo json_encode(["success" => true, "message" => "데이터가 정상적으로 저장되었습니다."]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => "데이터를 파일에 쓰지 못했습니다.",
            "hint" => "시놀로지 NAS의 파일/폴더 권한에서 HTTP 서비스 그룹(http)에게 쓰기 권한이 부여되었는지 확인해주세요."
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "허용되지 않는 요청 메서드입니다."]);
}
?>

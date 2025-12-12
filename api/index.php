<?php
session_start();
error_reporting( E_ALL ^ E_NOTICE );
mb_internal_encoding( 'UTF-8' );

// set content return type
header( 'Content-Type: application/json; charset=utf-8' );

// Setting up some server access controls to allow people to get information
header( "Access-Control-Allow-Origin: *" );
header( 'Access-Control-Allow-Methods:  POST, GET' );

require realpath( '../../dv-config.php' );
require DEV_PATH . '/classes/db.class.v2.php';
require DEV_PATH . '/functions/global.php';

// สำหรับ dev parameter (อนุญาตเฉพาะ alphanumeric)
$GET_DEV = sanitizeGetParam( 'dev', 'alphanumeric', '', 50 );

$json_data['data'] = [];

switch ( $GET_DEV ) {

    case 'getdocinfo':
        $doc_code = sanitizeGetParam( 'code', 'alphanumeric', '' );
        $action   = sanitizeGetParam( 'action', 'alphanumeric', '' );

        if ( empty( $doc_code ) ) {
            http_response_code( 400 );
            $json_data['status']  = 'error';
            $json_data['message'] = 'ไม่ได้ระบุรหัสเอกสาร';
            break;
        }

        // เช็คว่า action = scan ถึงจะบวกยอด
        if ( $action === 'scan' ) {
            $stmtCount = "UPDATE documents SET view_count = view_count + 1 WHERE document_code = ?";
            CON::updateDB( [$doc_code], $stmtCount );
        }

        // ดึงข้อมูลเอกสาร
        $docSql = "SELECT d.*, dt.type_name
                   FROM documents d
                   LEFT JOIN document_type dt ON d.type_id = dt.type_id
                   WHERE d.document_code = ?";
        $docResult = CON::selectArrayDB( [$doc_code], $docSql );

        if ( !$docResult || empty( $docResult ) ) {
            http_response_code( 404 );
            $json_data['status']  = 'error';
            $json_data['message'] = 'ไม่พบเอกสาร';
            break;
        }

        $doc = $docResult[0];

        // ดึงประวัติ (Timeline)
        $logSql = "SELECT l.*, u.fullname, u.username
                   FROM document_status_log l
                   LEFT JOIN users u ON l.action_by = u.user_id
                   WHERE l.document_id = ?
                   ORDER BY l.action_time DESC";
        $logs = CON::selectArrayDB( [$doc['document_id']], $logSql ) ?? [];

        $json_data['status'] = 'success';
        $json_data['doc']    = $doc;
        $json_data['logs']   = $logs;
        break;

    default:
        http_response_code( 400 );
        $json_data = [
            'success' => false,
            'message' => 'ไม่ได้ระบุหมายเหตุหรือ action:' . $GET_DEV . ' ไม่ถูกต้อง'
        ];
        break;
}

echo json_encode( $json_data, JSON_UNESCAPED_UNICODE );

// HTTP Response Codes Reference:
// 200 OK
// 201 CREATED  -  [POST,PUT]
// 204 NO CONTENT  -  [DELETE,PUT]
// 400 BAD REQUEST
// 401 UNAUTHORIZED
// 403 FORBIDDEN
// 404 NO FOUND
// 405 METHOD NOT ALLOWED
// 409 CONFLICT
// 500 INTERNAL SERVER ERROR
// 405 METHOD NOT ALLOWED
// 409 CONFLICT
// 500 INTERNAL SERVER ERROR

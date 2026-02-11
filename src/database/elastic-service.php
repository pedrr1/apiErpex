<?php
class Elasticsearch {
	private static string $host;

	public static function init(string $host):void{
		self::$host = $host;
	}
	public static function postDoc(string $index, array $document):void{
	$document = self::formatArray($document);
	$ch = curl_init(self::$host. "/$index/_doc");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // retorna resposta como string
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); // método POST
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($document)); // corpo em JSON
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // header JSON
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	self::authResponse($response, $httpCode);
	curl_close($ch);

	}

	private static function authResponse($response, $httpCode):void{
		if ($response === false){
			http_response_code(502);
            		exit;
		}
		if ($httpCode >= 400){
			http_response_code(502);
           		 exit;
		}
		$data = json_decode($response, true);
		if (json_last_error() !== JSON_ERROR_NONE){
			http_response_code(502);
            		exit;
		}

	}
	private static function formatArray($document):array{
		foreach($document as $key => $value){
			if (is_array($value)){
				$value = self::formatArray($value);
				if($value === []){
					unset($document[$key]);
					continue;
				}
			$document[$key] = $value;
			}
			elseif ($value === null){
				unset($document[$key]);
			}
		}
		return $document;
	}
}

?>

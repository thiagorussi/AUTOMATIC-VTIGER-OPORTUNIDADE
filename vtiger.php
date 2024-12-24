function save_module($module) {
		global $adb;
	
		// Verifica se o ID já foi processado
		static $processedIds = [];
		$potentialId = $this->id;
		if (in_array($potentialId, $processedIds)) {
			return; // Evita envio duplicado
		}
		$processedIds[] = $potentialId;
	
		// Consulta para obter todos os campos (tabela principal e customizados)
		$query = "
			SELECT vtiger_potential.*, vtiger_potentialscf.*
			FROM vtiger_potential
			LEFT JOIN vtiger_potentialscf ON vtiger_potential.potentialid = vtiger_potentialscf.potentialid
			WHERE vtiger_potential.potentialid = ?
		";
		$result = $adb->pquery($query, [$potentialId]);
		if ($adb->num_rows($result) === 0) {
			return; // Nenhum registro encontrado
		}
	
		// Obtém os campos como um array associativo
		$data = $adb->fetch_array($result);
	
		// Adiciona o ID ao payload
		$data['id'] = $potentialId;
	
		// Obter os nomes reais dos campos customizados
		$customFieldNames = [];
		$customFieldQuery = "
			SELECT fieldname, fieldlabel
			FROM vtiger_field
			WHERE tablename = 'vtiger_potentialscf' AND presence IN (0, 2)
		";
		$customFieldResult = $adb->pquery($customFieldQuery, []);
		while ($row = $adb->fetch_array($customFieldResult)) {
			$customFieldNames[$row['fieldname']] = $row['fieldlabel'];
		}
	
		// Ajustar os nomes dos campos no payload
		$payload = [];
		foreach ($data as $key => $value) {
			if (!is_numeric($key)) { // Ignora índices numéricos
				if (isset($customFieldNames[$key])) {
					// Substitui o campo customizado pelo nome real
					$payload[$customFieldNames[$key]] = $value;
				} else {
					// Usa o nome original para campos padrão
					$payload[$key] = $value;
				}
			}
		}
	
		// URL do Webhook
		$url = "https://c77d-200-225-115-232.ngrok-free.app/webhook";
	
		// Envia os dados via POST para o Webhook
		$options = [
			'http' => [
				'header'  => "Content-Type: application/json\r\n",
				'method'  => 'POST',
				'content' => json_encode($payload), // Envia campos com nomes reais
			],
		];
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
	
		// Log para depuração
		error_log("Webhook enviado: " . json_encode($payload));
		error_log("Resultado: " . $result);
	}




// ATUALIZADO:


function save_module($module) {
		global $adb;
	
		// Verifica se o ID já foi processado
		static $processedIds = [];
		$potentialId = $this->id;
		if (in_array($potentialId, $processedIds)) {
			return; // Evita envio duplicado
		}
		$processedIds[] = $potentialId;
	
		// Consulta para obter todos os campos (tabela principal e customizados)
		$query = "
			SELECT vtiger_potential.*, vtiger_potentialscf.*
			FROM vtiger_potential
			LEFT JOIN vtiger_potentialscf ON vtiger_potential.potentialid = vtiger_potentialscf.potentialid
			WHERE vtiger_potential.potentialid = ?
		";
		$result = $adb->pquery($query, [$potentialId]);
		if ($adb->num_rows($result) === 0) {
			return; // Nenhum registro encontrado
		}
	
		// Obtém os campos como um array associativo
		$data = $adb->fetch_array($result);
	
		// Obter os nomes reais dos campos customizados
		$customFieldNames = [];
		$customFieldQuery = "
			SELECT fieldname, fieldlabel
			FROM vtiger_field
			WHERE tablename = 'vtiger_potentialscf' AND presence IN (0, 2)
		";
		$customFieldResult = $adb->pquery($customFieldQuery, []);
		while ($row = $adb->fetch_array($customFieldResult)) {
			$customFieldNames[$row['fieldname']] = $row['fieldlabel'];
		}
	
		// Ajustar os nomes dos campos no payload
		$payload = [];
		foreach ($data as $key => $value) {
			if (!is_numeric($key)) { // Ignora índices numéricos
				$decodedKey = isset($customFieldNames[$key]) ? $customFieldNames[$key] : $key;
				$decodedKey = html_entity_decode($decodedKey, ENT_QUOTES, 'UTF-8'); // Decodifica caracteres especiais no título
				$decodedValue = html_entity_decode($value, ENT_QUOTES, 'UTF-8'); // Decodifica caracteres especiais no valor
				$payload[$decodedKey] = $decodedValue;
			}
		}
	
		// Consulta para obter o contato vinculado
		$contactQuery = "
			SELECT vtiger_contactdetails.*
			FROM vtiger_contactdetails
			JOIN vtiger_contpotentialrel ON vtiger_contpotentialrel.contactid = vtiger_contactdetails.contactid
			WHERE vtiger_contpotentialrel.potentialid = ?
		";
		$contactResult = $adb->pquery($contactQuery, [$potentialId]);
		if ($adb->num_rows($contactResult) > 0) {
			$contactData = $adb->fetch_array($contactResult);
			$payload['Contato Vinculado'] = [
				'Nome' => trim($contactData['firstname'] . ' ' . $contactData['lastname']),
				'Email' => $contactData['email'],
				'Telefone' => $contactData['phone'],
			];
		} else {
			$payload['Contato Vinculado'] = [
				'Nome' => '',
				'Email' => '',
				'Telefone' => '',
			];
		}
	
		// URL do Webhook
		$url = "https://90df-189-85-128-10.ngrok-free.app/webhook";
	
		// Envia os dados via POST para o Webhook
		$options = [
			'http' => [
				'header'  => "Content-Type: application/json\r\n",
				'method'  => 'POST',
				'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), // Garante codificação adequada
			],
		];
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
	
		// Log para depuração
		error_log("Webhook enviado: " . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		error_log("Resultado: " . $result);
	}

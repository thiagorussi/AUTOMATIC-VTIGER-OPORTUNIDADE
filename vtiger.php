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
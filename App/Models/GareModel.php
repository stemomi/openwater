<?php

namespace App\Models;

use PDO;
use DateTime;
use stdClass;
use \App\Models\ScontiModel;
use \App\Models\OpzioniAcquistoModel;

class GareModel extends \Core\Model
{
	public static function getAllGareAperte($escludi_combinate = 0)
	{
		$db = static::getDB();
		$sql = 'SELECT *,
						e.nome as nome_evento,
						e.foto as foto_evento,
						g.nome as nome_gara,
						g.ID as ID_gara,
						g.foto as foto_gara,
						e.vendita_boa as vendita_boa
				FROM gare as g
				LEFT JOIN eventi as e ON g.ID_evento = e.ID
				WHERE CURDATE() <= e.data
				AND e.data_apertura_iscrizioni <= CURDATE() ';

		if ($escludi_combinate == 1) $sql .= ' AND g.combinata = 0 ';

		$sql .= ' ORDER BY e.data';

		$stmt = $db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGare($escludi_combinate = 0)
	{
		$db = static::getDB();
		$sql = 'SELECT *,
						e.nome as nome_evento,
						e.foto as foto_evento,
						g.nome as nome_gara,
						g.ID as ID_gara,
						g.foto as foto_gara
				FROM gare as g
				LEFT JOIN eventi as e ON g.ID_evento = e.ID ';

		if ($escludi_combinate == 1) $sql .= ' WHERE g.combinata = 0';

		$sql .= ' ORDER BY e.nome ';

		$stmt = $db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGareConRisultatiVisibili()
	{
		$db = static::getDB();
		$sql = '
		SELECT  g.*,
				e.nome as nome_evento,
				e.foto as foto_evento,
				g.nome as nome_gara,
				g.ID as ID_gara,
				g.foto as foto_gara
		FROM gare as g
		LEFT JOIN eventi as e ON g.ID_evento = e.ID
		WHERE g.combinata = 0
		AND mostra_risultati = 1';

		$stmt = $db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGareAperteUtente($utente_id, $is_iscrizione_di_squadra = 0)
	{
		$sql = "
			SELECT 
				*,
				e.nome as nome_evento,
				e.foto as foto_evento,
				g.nome as nome_gara,
				g.ID as ID_gara,
				g.foto as foto_gara
		  	FROM iscrizioni as i
		  	LEFT JOIN gare as g ON i.ID_gara = g.ID
		  	LEFT JOIN eventi as e ON g.ID_evento = e.ID
		  	WHERE CURDATE() <= e.data
		  	AND i.ID_utente = :utente_id
		";

		if ($is_iscrizione_di_squadra)
			$sql .= " AND i.ID NOT IN (SELECT iscrizione_id FROM iscrizioni_iscrizioni_di_squadra)";
		
		$Db = static::getDB();

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':utente_id', $utente_id);

		$Stmt->execute();
		return $Stmt->fetchAll();
	}

	public static function getAllGarePassateUtente($utente_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT  e.ID as ID_evento,
									  e.nome as nome_evento,
									  e.foto as foto_evento,
									  g.nome as nome_gara,
									  g.ID as ID_gara,
									  g.foto as foto_gara,
									  g.combinata as gara_combinata,
									  g.mostra_risultati as mostra_risultati
							  FROM iscrizioni as i
							  INNER JOIN gare as g ON i.ID_gara = g.ID
							  INNER JOIN eventi as e ON g.ID_evento = e.ID
							  WHERE e.data < CURDATE()
							  AND i.ID_utente = :utente_id');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGareAperteUtenteStaffettaOCombinata($utente_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT *,
									  e.nome as nome_evento,
									  e.foto as foto_evento,
									  g.nome as nome_gara,
									  g.ID as ID_gara,
									  g.staffetta as A_g_staffetta,
									  g.combinata as A_g_combinata
							  FROM iscrizioni as i
							  LEFT JOIN gare as g ON i.ID_gara = g.ID
							  LEFT JOIN eventi as e ON g.ID_evento = e.ID
							  WHERE CURDATE() <= e.data
							  AND ( g.combinata = 1 OR g.staffetta = 1)
							  AND i.ID_utente = :utente_id');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGareAperteUtenteDaPagare($utente_id, $data_pagamento = '')
	{
		$db = static::getDB();
		$sql= '
		SELECT
		  *,
		  i.ID as ID_Iscrizione,
		  i.richiesta_boa as I_richiesta_boa,
		  e.nome as nome_evento,
		  e.foto as foto_evento,
		  g.nome as nome_gara,
		  g.ID as ID_gara,
		  g.foto as foto_gara,
		  g.ID_evento
		FROM iscrizioni as i
		LEFT JOIN gare as g ON i.ID_gara = g.ID
		LEFT JOIN eventi as e ON g.ID_evento = e.ID
		WHERE CURDATE() <= e.data
		AND i.ID_utente = :utente_id
		AND i.pagato = 0 ';

		if ($data_pagamento != '') $sql .= ' AND i.dataora < :data_pagamento';

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':utente_id', $utente_id);
		if ($data_pagamento != '') $stmt->bindValue(':data_pagamento', $data_pagamento);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGareDaCombinata($gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT ID_gara_collegata
							  FROM gare_collegate as gc
							  WHERE ID_gara_primaria = :gara_id');
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getAllGareDaCombinataConDettagli($gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT  e.ID as ID_evento,
									  e.nome as nome_evento,
									  g.nome as nome_gara,
									  g.ID as ID_gara,
									  g.mostra_risultati as mostra_risultati
							  FROM gare_collegate as gc
							  INNER JOIN gare as g ON gc.ID_gara_collegata = g.ID
							  INNER JOIN eventi as e ON g.ID_evento = e.ID
							  WHERE gc.ID_gara_primaria = :gara_id');
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function getGara($gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT *
							  FROM gare
							  WHERE ID = :gara_id');
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetch();
	}

	public static function getGaraConDettagli($gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT *,
									  e.nome as nome_evento,
									  g.nome as nome_gara,
									  g.data as data_gara,
									  g.ID as ID_gara
							  FROM gare as g
							  LEFT JOIN eventi as e ON g.ID_evento = e.ID 
							  WHERE g.ID = :gara_id');
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetch();
	}

	public static function getPrezzoGara($gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT  CASE
										WHEN CURDATE() <= data_prezzo1 THEN prezzo1 * (100 - sconto_prezzo1) / 100
										WHEN CURDATE() <= data_prezzo2 THEN prezzo2 * (100 - sconto_prezzo2) / 100
										WHEN CURDATE() <= data_prezzo3 THEN prezzo3 * (100 - sconto_prezzo3) / 100
										ELSE prezzo4 * (100 - sconto_prezzo4) / 100
									  END as prezzo_estratto,
									  CASE
										WHEN CURDATE() <= data_prezzo1 THEN 1
										WHEN CURDATE() <= data_prezzo2 THEN 2
										WHEN CURDATE() <= data_prezzo3 THEN 3
										ELSE 4
									  END as tipo_prezzo
							  FROM gare
							  WHERE ID = :gara_id');
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetch();
	}

	public static function getGaraUtente($utente_id, $gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT *
							  FROM iscrizioni
							  WHERE ID_utente = :utente_id
							  AND ID_gara = :gara_id');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetch();
	}

	public static function checkIscrizioneGara($utente_id, $gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT COUNT(*)
							  FROM iscrizioni
							  WHERE ID_utente = :utente_id
							  AND ID_gara = :gara_id');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public static function countUtenteIscrizioniNonPagatePerListaGare($utente_id, $lista_gare)
	{
		$db = static::getDB();
		$stmt = $db->prepare('
			SELECT COUNT(*)
			FROM iscrizioni
			WHERE ID_utente = :utente_id
			AND pagato = 0
			AND ID_gara IN (' . $lista_gare . ')'
		);
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public static function checkPagamentoGara($utente_id, $gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('SELECT COUNT(*)
							  FROM iscrizioni
							  WHERE ID_utente = :utente_id
							  AND ID_gara = :gara_id
							  AND pagato = 1');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public static function getTotaleDaPagareUtente($utente_id, $is_iscrizione_di_squadra = 0)
	{
		$sql = "
			SELECT SUM(
				CASE
					WHEN i.tipo_prezzo = 1 THEN g.prezzo1 * (100 - g.sconto_prezzo1) / 100
					WHEN i.tipo_prezzo = 2 THEN g.prezzo2 * (100 - g.sconto_prezzo2) / 100
					WHEN i.tipo_prezzo = 3 THEN g.prezzo3 * (100 - g.sconto_prezzo3) / 100
					ELSE g.prezzo4 * (100 - g.sconto_prezzo4) / 100
				 END
			)
			FROM iscrizioni as i
			LEFT JOIN gare as g ON i.ID_gara = g.ID
			LEFT JOIN eventi as e ON g.ID_evento = e.ID
			WHERE i.ID_utente = :utente_id
			AND CURDATE() <= e.data
			AND i.pagato = 0
		";

		if ($is_iscrizione_di_squadra)
			$sql .= " 
				AND capo_squadra_id IS NOT NULL
				AND i.ID NOT IN (SELECT iscrizione_id FROM iscrizioni_iscrizioni_di_squadra) 
			";
		else 
			$sql .= "
				AND capo_squadra_id IS NULL
			";

		$Db = static::getDB();

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':utente_id', $utente_id);

		$Stmt->execute();

		return $Stmt->fetchColumn();
	}

	public static function setGaraPagata($utente_id, $gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('UPDATE iscrizioni
							  SET pagato = 1
							  WHERE ID_utente = :utente_id
							  AND ID_gara = :gara_id');
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->execute();
	}

	public static function setGaraRichiestaBoa($utente_id, $gara_id, $richiesta_boa)
	{
		$db = static::getDB();
		$stmt = $db->prepare('
		  UPDATE iscrizioni
		  SET richiesta_boa = :richiesta_boa
		  WHERE ID_utente = :utente_id
		  AND ID_gara = :gara_id');

		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->bindValue(':richiesta_boa', $richiesta_boa);

		$stmt->execute();
	}

	public static function setUtenteGarePrezzoBoa($utente_id, $iscrizione_id = 0)
	{
		$Db = static::getDB();

		$sql = "
			UPDATE iscrizioni
			SET prezzo_boa = :prezzo_boa
			WHERE ID_utente = :utente_id
			AND richiesta_boa = 1
		";

		if ($iscrizione_id)
			$sql .= " AND ID = :iscrizione_id ";

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':utente_id', $utente_id);
		$Stmt->bindValue(':prezzo_boa', PREZZO_BOA);

		if ($iscrizione_id)
			$Stmt->bindValue(':iscrizione_id', $iscrizione_id);

		$Stmt->execute();
	}

	public static function checkTransazionePaypal($ID_transazione_paypal)
	{
		$db = static::getDB();

		$stmt = $db->prepare('
		  SELECT COUNT(*)
		  FROM pagamenti
		  WHERE ID_transazione_paypal = :ID_transazione_paypal');

		$stmt->bindValue(':ID_transazione_paypal', $ID_transazione_paypal);
		$stmt->execute();

		return $stmt->fetchColumn();
	}

	public static function insertPagamento(
	  $utente_id,
	  $data_bonifico,
	  $importo,
	  $tipo,
	  $file,
	  $pagato_boa,
	  $ID_transazione_paypal = '',
	  $codice_coupon = '',
	  $pagato_da_credito = 0.00)
	{
		if (preg_match('/^(\d+){2}\/(\d+){2}\/(\d+){4}$/', $data_bonifico))
			$data_bonifico= DateTime::createFromFormat('d/m/Y', $data_bonifico)->format('Y-m-d');
		else
			$data_bonifico = '';

		$db = static::getDB();
		$stmt = $db->prepare('
		  INSERT INTO pagamenti
		  (
			ID_utente,
			dataora,
			data_pagamento,
			importo,
			tipo,
			file,
			pagato_boa,
			ID_transazione_paypal,
			codice_coupon,
			pagato_da_credito
		  )
		  VALUES
		  (
			:utente_id,
			NOW(),
			:data_bonifico,
			:importo,
			:tipo,
			:file,
			:pagato_boa,
			:ID_transazione_paypal,
			:codice_coupon,
			:pagato_da_credito
		  )');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':data_bonifico', $data_bonifico);
		$stmt->bindValue(':importo', $importo);
		$stmt->bindValue(':tipo', $tipo);
		$stmt->bindValue(':file', $file);
		$stmt->bindValue(':pagato_boa', $pagato_boa);
		$stmt->bindValue(':ID_transazione_paypal', $ID_transazione_paypal);
		$stmt->bindValue(':codice_coupon', $codice_coupon);
		$stmt->bindValue(':pagato_da_credito', $pagato_da_credito);
		$stmt->execute();

		return $db->lastInsertId();
	}

	public static function insertGaraUtente
	(
	 	$utente_id,
	  	$gara_id,
	  	$tipo_prezzo,
	  	$richiesta_boa = false,
	  	$capo_squadra_id = null
	)
	{
		$sql = "
			INSERT INTO iscrizioni
			(
				ID_utente,
				ID_gara,
				dataora,
				tipo_prezzo,
				richiesta_boa,
				capo_squadra_id
			)
			VALUES
			(
				:utente_id,
				:gara_id,
				NOW(),
				:tipo_prezzo,
				:richiesta_boa,
				:capo_squadra_id
			)
		";

		$Db = static::getDB();

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':utente_id', $utente_id);
		$Stmt->bindValue(':gara_id', $gara_id);
		$Stmt->bindValue(':tipo_prezzo', $tipo_prezzo);
		$Stmt->bindValue(':richiesta_boa', $richiesta_boa);
		$Stmt->bindValue(':capo_squadra_id', $capo_squadra_id);

		$Stmt->execute();
	}

	public static function salvaPagamentoGara($utente_id, $gara_id, $pagamento_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('INSERT INTO pagamenti_gare
							  (ID_utente, ID_gara, ID_pagamento)
							  VALUES
							  (:utente_id, :gara_id, :pagamento_id)');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->bindValue(':pagamento_id', $pagamento_id);
		$stmt->execute();
	}

	public static function insertBoaPagataUtente($utente_id, $pagamento_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('INSERT INTO pagamenti_boe
							  (ID_utente, ID_pagamento)
							  VALUES
							  (:utente_id, :pagamento_id)');
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':pagamento_id', $pagamento_id);
		$stmt->execute();
	}

	public static function getUtenteTotaleBoeDaPagare($utente_id)
	{
		$db = static::getDB();

		$sql = "
		SELECT COUNT(*)
		FROM iscrizioni AS i
		LEFT JOIN gare as g ON i.ID_gara = g.ID
		WHERE i.ID_utente = :utente_id
		AND i.richiesta_boa = 1
		AND i.prezzo_boa = 0
		AND g.data >= CURDATE()";

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':utente_id', $utente_id);

		$stmt->execute();

		return $stmt->fetchColumn();
	}

	public static function getUtenteStaffetteDaPagare($utente_id)
	{
		$db = static::getDB();

		$sql = "
		SELECT
		  i.*,
		  g.ID_evento
		FROM iscrizioni as i
		LEFT JOIN gare as g ON g.ID = i.ID_gara
		LEFT JOIN eventi as e ON g.ID_evento = e.ID
		WHERE i.ID_utente = :utente_id
		AND g.staffetta = 1
		AND i.pagato = 0
		AND CURDATE() <= e.data
		AND e.data_apertura_iscrizioni <= CURDATE()";

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':utente_id', $utente_id);

		$stmt->execute();

		return $stmt->fetchAll();
	}

	public static function checkUtenteStaffettaDaScontare($utente_id, $iscrizione_id, $evento_id)
	{
		$db = static::getDB();

		$sql = "
		SELECT COUNT(*)
		FROM iscrizioni as i
		LEFT JOIN gare as g ON g.ID = i.ID_gara
		LEFT JOIN eventi as e ON g.ID_evento = e.ID
		WHERE i.ID_utente = :utente_id
		AND i.ID <> :iscrizione_id
		AND g.ID_evento = :evento_id
		AND g.staffetta = 0
		AND i.pagato = 1";

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':iscrizione_id', $iscrizione_id);
		$stmt->bindValue(':evento_id', $evento_id);

		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public static function impostaStaffettaComeScontata($iscrizione_id)
	{
	  $db = static::getDB();

	  $sql = "
	  UPDATE iscrizioni
	  SET sconto_staffetta = :sconto_staffetta
	  WHERE ID = :iscrizione_id";

	  $stmt = $db->prepare($sql);
	  $stmt->bindValue(':sconto_staffetta', SCONTO_STAFFETTA);
	  $stmt->bindValue(':iscrizione_id', $iscrizione_id);

	  $stmt->execute();
	}

	public static function checkUtenteAcquistoMaglietta($utente_id, $quantita)
	{
        $sql = "
            SELECT COUNT(*) 
            FROM acquisti_magliette
            WHERE IDUtente = :utente_id
            AND quantita = :quantita
            AND IDPagamento IS NULL
        ";

		$Db = static::getDB();

		$Query = $Db->prepare($sql);

		$Query->bindValue(':utente_id', $utente_id);
		$Query->bindValue(':quantita', $quantita);

		$Query->execute();

        return $Query->fetchColumn();
	}

    public function getUtenteAcquistoMaglietta($utente_id)
    {
        $sql = "
            SELECT *
            FROM acquisti_magliette
            WHERE IDUtente = :utente_id
            AND IDPagamento IS NULL
            LIMIT 1
        ";

		$Db = static::getDB();

		$Query = $Db->prepare($sql);

		$Query->bindValue(':utente_id', $utente_id);

		$Query->execute();

        return $Query->fetch();
    }

	public static function addUtenteAcquistoMaglietta($utente_id, $quantita, $importo_totale)
	{
		$db = static::getDB();

		$sql = "
		INSERT INTO acquisti_magliette
		(
		  IDUtente,
		  quantita,
		  importo_totale,
		  dataora
		)
		VALUES
		(
		  :utente_id,
		  :quantita,
		  :importo_totale,
		  :dataora
		)";

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':utente_id', $utente_id);
		$stmt->bindValue(':quantita', $quantita);
		$stmt->bindValue(':importo_totale', $importo_totale);
		$stmt->bindValue(':dataora', date('Y-m-d H:i:s'));

		$stmt->execute();
		
		return $db->lastInsertId();
	}

	public function getUtenteTotaleMaglietteDaPagare($utente_id)
	{
		$db = static::getDB();

		$sql = '
		SELECT SUM(importo_totale)
		FROM acquisti_magliette
		WHERE IDUtente = :utente_id
		AND IDPagamento IS NULL';

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':utente_id', $utente_id);

		$stmt->execute();

		return $stmt->fetchColumn();
	}

	public static function impostaUtenteMaglietteComePagate($utente_id, $IDPagamento)
	{
	  $db = static::getDB();

	  $sql = "
	  UPDATE acquisti_magliette
	  SET IDPagamento = :IDPagamento
	  WHERE IDUtente = :utente_id
	  AND IDPagamento IS NULL";

	  $stmt = $db->prepare($sql);
	  $stmt->bindValue(':utente_id', $utente_id);
	  $stmt->bindValue(':IDPagamento', $IDPagamento);

	  $stmt->execute();
	}

	public static function getRisultatoGara($risultato_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('
			SELECT
				rg.*,
				g.nome as A_gara_nome,
				e.nome as A_evento_nome,
				g.ID as A_gara_ID,
				e.foto as A_evento_foto,
				u.foto_profilo as A_utenti_foto_profilo,
				u.donatore_avis as A_utenti_donatore_avis,
				u.sesso as A_utenti_sesso,
				s.foto as A_foto_squadra
			FROM risultati_gare as rg
			LEFT JOIN gare as g ON g.ID = rg.ID_gara
			LEFT JOIN eventi as e ON e.ID = g.ID_evento
			LEFT JOIN utenti as u ON u.ID = rg.tessera
			LEFT JOIN squadre as s ON u.IDSquadra = s.ID
			WHERE rg.ID = :risultato_id
			LIMIT 1'
		);
		$stmt->bindValue(':risultato_id', $risultato_id);
		$stmt->execute();

		return $stmt->fetch();
	}

	public static function getTotalePartecipantiGara($gara_id)
	{
		$db = static::getDB();
		$stmt = $db->prepare('
			SELECT COUNT(*)
			FROM risultati_gare
			WHERE ID_gara = :gara_id'
		);
		$stmt->bindValue(':gara_id', $gara_id);
		$stmt->execute();

		return $stmt->fetchColumn();
	}

	public function getAcquistoMaglietteUtente($utente_id)
	{
		$Db = static::getDB();

		$sql = "
			SELECT 
				am.ID,
				am.dataora as data_acquisto,
				p.dataora as data_pagamento,
				am.quantita,
				am.importo_totale
			FROM acquisti_magliette as am
			JOIN pagamenti as p ON am.IDPagamento = p.ID
			WHERE am.IDUtente = :utente_id
			AND am.confermato = 0
		";

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':utente_id', $utente_id);

		$Stmt->execute();

		return $Stmt->fetch();
	}

	public function aggiornaCampoConfermato($acquisto_magliette_id)
	{
		$Db = static::getDB();

		$sql = "
			UPDATE acquisti_magliette
			SET confermato = 1
			WHERE ID = :acquisto_magliette_id
		";

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':acquisto_magliette_id', $acquisto_magliette_id);

		$Stmt->execute();
	}

	public function insertIscrizioneDiSquadra
	(
		$capo_squadra_id,
		$pagato,
		$totale,
		$squadra_id
	)
	{
		$sql = "
			INSERT INTO iscrizioni_di_squadra
			(
				data,
				capo_squadra_id,
				pagato,
				totale,
				squadra_id
			)
			VALUES
			(
				:data,
				:capo_squadra_id,
				:pagato,
				:totale,
				:squadra_id
			)
		";

		$Db = static::getDB();

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':data', date('Y-m-d H:i:s'));
		$Stmt->bindValue(':capo_squadra_id', $capo_squadra_id);
		$Stmt->bindValue(':pagato', $pagato, PDO::PARAM_BOOL);
		$Stmt->bindValue(':totale', $totale);
		$Stmt->bindValue(':squadra_id', $squadra_id);

		$Stmt->execute();

		return $Db->lastInsertId();
	}

	public function getIscrizioniDiSquadra($capo_squadra_id)
	{
		$sql = "
			SELECT *
			FROM iscrizioni as i
			WHERE capo_squadra_id = :capo_squadra_id
			AND i.ID NOT IN 
			(
				SELECT iscrizione_id
				FROM iscrizioni_iscrizioni_di_squadra
			)
		";

		$Db = static::getDB();

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':capo_squadra_id', $capo_squadra_id);

		$Stmt->execute();

		return $Stmt->fetchAll();
	}

	public function collegaIscrizioneAIscrizioneDiSquadra($iscrizione_id, $iscrizione_di_squadra_id)
	{
		$sql = "
			INSERT INTO iscrizioni_iscrizioni_di_squadra
			(
				iscrizione_id,
				iscrizione_di_squadra_id
			)
			VALUES
			(
				:iscrizione_id,
				:iscrizione_di_squadra_id
			)
		";

		$Db = static::getDB();

		$Stmt = $Db->prepare($sql);

		$Stmt->bindValue(':iscrizione_id', $iscrizione_id);
		$Stmt->bindValue(':iscrizione_di_squadra_id', $iscrizione_di_squadra_id);

		$Stmt->execute();
	}

	// DA QUI FUNZIONI
	public static function EstraeGareAperteUtente($Utente, $is_iscrizione_di_squadra = 0)
	{
		$OpzioniAcquistoClass = new OpzioniAcquistoModel();
		$ScontiClass = new ScontiModel();
		$GareClass = new GareModel();

		$GareAperteUtente= self::getAllGareAperteUtente($Utente->ID);
		$Eventi = [];
		$EventiConDettagli = []; // Con Array Gare invece di inserirle direttamente nel primo indice, in modo da poter aggiungere dettagli all'evento

		foreach($GareAperteUtente as $gara)
		{
			// Estrae gara utente
			$GaraUtente= self::getGaraUtente($Utente->ID, $gara->ID_gara);

			if ($GaraUtente)
			{
				$gara->iscritto= 1;
				$gara->pagato= $GaraUtente->pagato;
				$gara->tipo_prezzo= $GaraUtente->tipo_prezzo;
                $gara->iscrizione_id = $GaraUtente->ID;
			}
			else
			{
				$gara->iscritto= 0;
				$gara->pagato= 0;
				$gara->tipo_prezzo= 0;
			}
			
			$Eventi[$gara->ID_evento][] = $gara;

			// Con dettagli
			$EventiConDettagli[$gara->ID_evento]['Gare'][] = $gara;

			// Inizializza variabili boa evento se non presenti
			if ( !isset($EventiConDettagli[$gara->ID_evento]['richiesta_boa']) )
				$EventiConDettagli[$gara->ID_evento]['richiesta_boa'] = 0;

			if ( !isset($EventiConDettagli[$gara->ID_evento]['prezzo_boa']) )
				$EventiConDettagli[$gara->ID_evento]['prezzo_boa'] = 0.00;

			// Controlla se le gare di questo evento hanno una richiesta per la boa, eventualmente pagata
			foreach($GareAperteUtente as $gara_check_boa)
			{
				if ($gara_check_boa->ID_evento == $gara->ID_evento)
				{
					if ($EventiConDettagli[$gara->ID_evento]['richiesta_boa'] == 0 && $gara_check_boa->richiesta_boa == 1)
						$EventiConDettagli[$gara->ID_evento]['richiesta_boa'] = 1;

					if ($EventiConDettagli[$gara->ID_evento]['prezzo_boa'] == 0.00 && $gara_check_boa->prezzo_boa > 0)
						$EventiConDettagli[$gara->ID_evento]['prezzo_boa'] = $gara_check_boa->prezzo_boa;
				}
			}

			// Aggiunge opzioni acquisto evento
			$OpzioniAcquistoEvento = $OpzioniAcquistoClass->getOpzioniAcquistoEvento($gara->ID_evento);

			foreach($OpzioniAcquistoEvento as $opzione_acquisto_evento)
			{
				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['id'] = $opzione_acquisto_evento->eoa_ID;
				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['prezzo'] = $opzione_acquisto_evento->oa_prezzo;
				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['obbligatorio'] = $opzione_acquisto_evento->eoa_obbligatorio;

				// Cerca se è stato acquistato ed eventualmente pagato
				$AcquistoOpzione = $OpzioniAcquistoClass->getAcquistoOpzioneEventoUtente($Utente->ID, $opzione_acquisto_evento->eoa_ID);
				$opzione_acquisto_evento_acquistato = 0;
				$opzione_acquisto_evento_pagato = 0;
                $opzione_acquisto_aoae_id = 0;
                $ha_opzione_acquisto = 0;

				if ($AcquistoOpzione)
				{
					$opzione_acquisto_evento_acquistato = 1;

					if ($AcquistoOpzione->pagato == 1)
						$opzione_acquisto_evento_pagato = 1;

                    // Id presente nella tabella acquisti_opzioni_acquisto_eventi che serve per la cancellazione 
                    $opzione_acquisto_aoae_id = $AcquistoOpzione->ID;

                    $ha_opzione_acquisto = 1;
				}

				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['acquistato'] = $opzione_acquisto_evento_acquistato;
				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['pagato'] = $opzione_acquisto_evento_pagato;
				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['pagato'] = $opzione_acquisto_evento_pagato;
				$EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['opzione_acquisto_aoae_id'] = $opzione_acquisto_aoae_id;
                $EventiConDettagli[$gara->ID_evento]['OpzioniAcquisto']['ha_opzione_acquisto'] = $ha_opzione_acquisto;
			}
		}

		// Estrae sconti combo gare
		$ArrayGareScontoComboEffettuato = $ScontiClass->getUtenteGareScontoComboEffettuato($Utente->ID);

		// Flagga gare per segnalare l'applicazione di uno sconto combo gara
		foreach ($EventiConDettagli as $evento)
		{
			foreach($evento['Gare'] as &$gara)
			{
				if (in_array($gara->ID_gara, $ArrayGareScontoComboEffettuato))
					$gara->sconto_combo_gara_applicato = 1;
				else
					$gara->sconto_combo_gara_applicato = 0;
			}
		}

		// Calcola totale da pagare
		$TotaleDaPagareUtente= self::getTotaleDaPagareUtente($Utente->ID, $is_iscrizione_di_squadra);
		if (!$TotaleDaPagareUtente) $TotaleDaPagareUtente= "0.00";

		$Risposta= new stdClass();
		$Risposta->Eventi= $Eventi;
		$Risposta->EventiConDettagli= $EventiConDettagli;
		$Risposta->TotaleDaPagareUtente = $TotaleDaPagareUtente;
		return $Risposta;
	}

	public static function getUtenteStaffetteDaScontare($utente_id)
	{
		// Sconto € 5 per staffette facenti parte di un evento a cui sono state già pagate altre gare
		$TotaleScontoStaffette = 0.00;
		$StaffetteDaPagareUtente = self::getUtenteStaffetteDaPagare($utente_id);

		foreach ($StaffetteDaPagareUtente as $staffetta_da_pagare)
		{
		  $DaScontare = self::checkUtenteStaffettaDaScontare(
			$utente_id,
			$staffetta_da_pagare->ID,
			$staffetta_da_pagare->ID_evento
		  );

		  if ($DaScontare > 0)
			$TotaleScontoStaffette += SCONTO_STAFFETTA;
		}

		$TotaleScontoStaffette = number_format($TotaleScontoStaffette, 2, '.', '');

		return $TotaleScontoStaffette;
	}

	public function segnaStaffettaSeDaScontare($gara)
	{
		if ($gara->staffetta == 1)
		{
			$DaScontare = self::checkUtenteStaffettaDaScontare(
				$gara->ID_utente,
				$gara->ID_Iscrizione,
				$gara->ID_evento
			);

			if ($DaScontare > 0)
				self::impostaStaffettaComeScontata($gara->ID_Iscrizione);
		}
	}

	public function getStringaEmailMaglietteAcquistate($utente_id)
	{
		$MaglietteAcquistate = '';

		if ( self::checkUtenteAcquistoMaglietta($utente_id, 1) )
			$MaglietteAcquistate = '<br /><div>Magliette acquistate: 1</div>';
		elseif ( self::checkUtenteAcquistoMaglietta($utente_id, 2) )
			$MaglietteAcquistate = '<br /><div>Magliette acquistate: 2</div>';
		elseif ( self::checkUtenteAcquistoMaglietta($utente_id, 3) )
			$MaglietteAcquistate = '<br /><div>Magliette acquistate: 3</div>';

		return $MaglietteAcquistate;
	}

	
}

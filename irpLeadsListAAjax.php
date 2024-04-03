<?php
require 'config.php';

if (isset($_POST['user']) && array_key_exists($_POST['user'], $access_array) && in_array('dostep_koordynator', $access_array[$_POST['user']])) {
    $link = mysqlConnect();
    $data = array();

/*
 * Szanse sprzedazy (id_procesu)
 *
 * 3M
 * 15 - MPK,
 * 16 - SU,
 * 18 - ZM,
 * 21 - NP,
 * 23 - UF,
 * 38 - NP (po kwalifikacji)
 * 54 - MPK (po skończonym PRCLF)
 * 55 - FPK
 * 62 - MagMark
 * 
 * IRP
 * 9  - IRP - SP
 * 46 - IRP
 * 48 - IRP - 1P
 * 49 - IRP - 2P
 * 51 - IRP 1P - PR
 * 52 - IRP 2P - PR
 * 61 - IRP - 3P
 * 
 * 
 * Tabela `produkty`
 * ____________________
 * | id  | nazwa      |
 * | 116 |Lekcja 1    |
 * | 117 |Lekcja 2    |
 * | 118 |Lekcja 3    |
 * | 119 |Lekcja 4    |
 * | 120 |Lekcja 5    |
 * | 121 |Lekcja 6    |
 * | 122 |Lekcja 7    |
 * | 123 |Lekcja 8    |
 * | 124 |Lekcja 9    |
 * | 125 |Lekcja 10   |
 * | 126 |Lekcja 11   |
 * | 127 |Lekcja 12   |
 * ____________________
 */
    $query = 
"DROP TEMPORARY TABLE IF EXISTS __wykluczenia0;
CREATE TEMPORARY TABLE __wykluczenia0 (klient_id INT NOT NULL, PRIMARY KEY (klient_id))
AS
SELECT zad.klient_id FROM zadania zad WHERE zad.typ IN(86,52,32,106,108,110,111,112,114) AND zad.status = 'aktywne'
UNION
SELECT ss.klient_id FROM `szanseSprzedazy` ss WHERE ss.id_procesu IN (58) AND ss.status IN ('wygrana')
UNION 
SELECT  ss.klient_id FROM `szanseSprzedazy` ss WHERE ss.id_procesu IN (9,46,48,49,51,52)
UNION 
SELECT ss.klient_id FROM `szanseSprzedazy` ss WHERE ss.id_procesu IN (15,16,18,21,23,38,54,55,62,40,50,53,65) AND ss.status IN ('otwarta')
UNION 
SELECT ss.klient_id FROM `szanseSprzedazy` ss WHERE ss.id_procesu IN (40,50,53,65) AND ss.status IN ('wygrana')
UNION 
SELECT t.klient_id FROM `tagi` t WHERE t.tagId IN (10,11,24,32)
UNION 
SELECT ss.klient_id FROM `szanseSprzedazy` ss WHERE ss.id_procesu IN (40,50,53,65,62,15,16,18,21,23,38,54,55) 
    AND ss.status IN('przegrana') AND ss.data_zakonczenia > date_sub(curdate(),interval 1 MONTH)	
UNION 
SELECT z.klient_id FROM zamowienia z JOIN faktury f ON f.id_zamowienia = z.id WHERE z.status IN('do opłacenia') AND z.zaplacone = 'nie' AND f.rodzaj_faktury = 'VAT'
	AND DATE_ADD(f.data_wystawienia,INTERVAL f.termin DAY) < CURDATE() GROUP BY z.klient_id HAVING COUNT(*) > 2;

-- wykluczamy też duplikaty klientow
DROP TEMPORARY TABLE IF EXISTS __wykluczenia1;
CREATE TEMPORARY TABLE __wykluczenia1 (klient_id INT NOT NULL)
AS SELECT klient2_id AS klient_id FROM __wykluczenia0 w join klienci_duplikaty kd ON w.klient_id = kd.klient1_id AND kd.czy_duplikat = 1;

DROP TEMPORARY TABLE IF EXISTS __wykluczenia2;
CREATE TEMPORARY TABLE __wykluczenia2 (klient_id INT NOT NULL)
AS SELECT klient1_id AS klient_id  FROM __wykluczenia0 w join klienci_duplikaty kd ON w.klient_id = kd.klient2_id AND kd.czy_duplikat = 1;

DROP TEMPORARY TABLE IF EXISTS __wykluczenia;
CREATE TEMPORARY TABLE __wykluczenia (klient_id INT NOT NULL, PRIMARY KEY (klient_id))
AS SELECT klient_id FROM __wykluczenia0
UNION SELECT klient_id FROM __wykluczenia1
UNION SELECT klient_id FROM __wykluczenia2;


SELECT k.id, k.imie, k.nazwisko, k.email, k.telefon, k.level
FROM `klienci` k 
JOIN uczestnicyProgramu u ON u.klient_id = k.id
JOIN (
    SELECT uczestnik_id, MAX(produkt_id) as max 
    FROM `uczestnicyProgramuProdukty` t1 
    WHERE t1.status = 'wyslane' 
    GROUP BY uczestnik_id 
    HAVING max=118
) AS t2 ON t2.uczestnik_id = u.uczestnik_id
WHERE
    k.level = 'uczestnik' 
    AND k.status = 'aktywny'
    AND NOT EXISTS(SELECT 1 FROM __wykluczenia w WHERE w.klient_id = k.id);
    
    SELECT k.id, k.imie, k.nazwisko, k.email, k.telefon, k.level, p.nazwa AS produkt_name
    FROM `klienci` k 
    JOIN uczestnicyProgramu u ON u.klient_id = k.id
    JOIN (
        SELECT uczestnik_id, MAX(produkt_id) as max 
        FROM `uczestnicyProgramuProdukty` t1 
        WHERE t1.status = 'wyslane' 
        GROUP BY uczestnik_id 
        HAVING max>118
    ) AS t2 ON t2.uczestnik_id = u.uczestnik_id
    JOIN produkty p ON p.id = max
    WHERE
        k.level = 'uczestnik' 
        AND k.status = 'aktywny'
        AND NOT EXISTS(SELECT 1 FROM __wykluczenia w WHERE w.klient_id = k.id);
    
    ";

    $result = mysqli_multi_query($link, $query);
    for($i=1;$i<8;$i++){
        mysqli_next_result($link);
    }
    
    $i = 0;
    while (mysqli_more_results($link) && mysqli_next_result($link)) {
        
        if ($result = mysqli_store_result($link)) {
            $data[$i] = [];
            while ($w = mysqli_fetch_assoc($result)) {
                $entry = array(
                    'klient_id' => $w['id'],
                    'klient'    => $w['imie'] . ' ' . $w['nazwisko'],
                    'telefon'   => $w['telefon'],
                    'email'     => $w['email'],
                    'level'     => $w['level'],
                );

                if (isset($w['produkt_name'])) {
                    $entry['produkt_name'] = $w['produkt_name'];
                }

                $data[$i][] = $entry;
            }
            $result->free();
        }
        $i++;
    }

    echo json_encode(array("status" => "success", "message" => "OK", "dane" => $data));
    mysqli_close($link);
} else { //osoba nie ma uprawnien
    echo json_encode(array("status" => "error", "message" => "Nie masz uprawnień do tej strony."));
}

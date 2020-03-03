<?php
/**
 * Update employees via cronjob
 *
 * @cronjob    0 0,12 * * * /usr/local/bin/php /home/viaexpos/employees.viaexpo.site/cron.php get
 * @cronjob    0 23,11 * * * /usr/local/bin/php /home/viaexpos/employees.viaexpo.site/cron.php canBeDownload
 * @cronjob    * / 18 * * * * /usr/local/bin/php /home/viaexpos/employees.viaexpo.site/cron.php getFromBadHost
 * @author    Pavel Kenarov
 */

error_reporting(E_ALL);

if (php_sapi_name() !== 'cli') {
    // Can only be executed via CLI
    die();
}

require_once 'includes/config.php';

class cron
{

    public $admin = 'admin@gmail.com';
    public $avatar = 'user.svg';

    public function __construct($argv)
    {

        if (empty($argv[1]))
            exit('Method not specified!');

        // Bad hosts list - their images will be processed additionally by another cron
        $this->badHosts = array(
            'lorempixel.com'
        );

        $db = db::getInstance();
        $this->db = $db->getConnection();

        switch ($argv[1]) {
            case 'get':
                $this->_getFromApi();
                break;
            case 'canBeDownload':
                $this->_canDownloadAvatars();
                break;
            case 'getFromBadHost':
                $this->_downloadFromBadHost();
                break;
            case 'processCheckAndGetFromBadHost':
				
				$current_process_id = $this->getProcess();	
				if(empty($current_process_id)){
					$processId = exec("php /root_path/cron.php canBeDownload > /dev/null 2>&1 & echo $!;");
					$this->setProcess($process_id);					
				}else{
					if (!file_exists("/proc/{$current_process_id}")) {
						$processId = exec("php /root_path/cron.php canBeDownload > /dev/null 2>&1 & echo $!;");
						$this->setProcess($process_id);	
					}
				}

				die;

                break;
        }

    }

    private function _getFromApi()
    {

        $getJson = api::connect(20);
        if (empty($getJson)) {
            $this->_logError('Cron error processing update backup file!', serialize($getJson));
        } else {

            $inserted = 0;
            foreach ($getJson as $emp) {

                if (empty($emp->name) || empty($emp->title) || empty($emp->company)) {
                    continue;
                }

                $image = $this->avatar;
                $status = 'finished';
                if (!empty($emp->avatar)) {

                    if (preg_match('/^data:image\/(\w+);base64,/', $emp->avatar, $type)) {
                        $data = substr($emp->avatar, strpos($emp->avatar, ',') + 1);
                        $type = strtolower($type[1]);

                        if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                            $data = base64_decode($data);
                            if ($data !== false) {
                                file_put_contents(DIR_AVATAR . $emp->uuid . '.' . $type, $data);
                                $image = $emp->uuid . '.' . $type;
                            }
                        }
                    } else if (filter_var($emp->avatar, FILTER_VALIDATE_URL)) {
                        // Images coming from urls will store to be downloaded from second cron
                        $status = 'avatar';
                        $image = $emp->avatar;
                        $purl = parse_url($emp->avatar);
                        if (!empty($purl['host'])) {
                            if (in_array($purl['host'], $this->badHosts)) {
                                // Avatars coming from bad hosts will store with different status
                                $status = 'badhost';
                            }
                        }
                    }
                }

                $cv = '';
                if (!empty($emp->bio)) {
                    $cv = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $emp->bio);
                    $cv = htmlspecialchars(strip_tags($cv), ENT_QUOTES, 'UTF-8');
                }

                // Checked for changes and made update via "ON DUPLICATE KEY UPDATE"
                $this->db->query("INSERT INTO `employees` (`uuid`, `bio`, `company`, `name`, `title`, `avatar`, `status`) VALUES (
					'" . $this->db->real_escape_string($emp->uuid) . "', 
					'" . $this->db->real_escape_string($cv) . "',
					'" . $this->db->real_escape_string(htmlspecialchars($emp->company, ENT_QUOTES, 'UTF-8')) . "',
					'" . $this->db->real_escape_string(htmlspecialchars($emp->name, ENT_QUOTES, 'UTF-8')) . "',
					'" . $this->db->real_escape_string(htmlspecialchars($emp->title, ENT_QUOTES, 'UTF-8')) . "',
					'" . $this->db->real_escape_string($image) . "', 
					'" . $status . "'
				)
				ON DUPLICATE KEY UPDATE
				 `bio` = values(bio),
				 `company` = values(company),
				 `name` = values(name),
				 `title` = values(title),
				 `avatar` = values(avatar),
				 `status` = values(status)
				 ");

                if (!empty($this->db->affected_rows))
                    $inserted++;

            }

        }

        if ($inserted < 600) {
            $this->_logError('Cron error too little affected rows on employees DB table!', serialize($getJson));
            return false;
        }

        return true;
    }

    private function _canDownloadAvatars()
    {

        $get = $this->db->query(' SELECT * FROM `employees` WHERE `status` = "avatar" ORDER BY `id` desc');
        while ($e = $get->fetch_assoc()) {

            if ($this->_canBeDownload($e['avatar'])) {
                $this->db->query("UPDATE `employees` SET `status` = 'badhost' WHERE `id` = '" . (int)$e['id'] . "' ");
            } else {
                $this->db->query("UPDATE `employees` SET `status` = 'finished', `avatar` = '" . $this->avatar . "' WHERE `id` = '" . (int)$e['id'] . "' ");
            }

        }

    }

    private function _downloadFromBadHost()
    {

        $get = $this->db->query(' SELECT * FROM `employees` WHERE `status` = "badhost" ORDER BY rand() limit 18 ');
        while ($e = $get->fetch_assoc()) {
            //var_dump($e);
            //$this->db->autocommit(FALSE);

            $image = $e['uuid'] . '.jpg';
            if (file_exists(DIR_AVATAR . $image)) {
                $this->db->query("UPDATE `employees` SET `status` = 'finished', `avatar` = '" . $image . "' WHERE `id` = '" . (int)$e['id'] . "' ");
                continue;
            }


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $e['avatar']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            $output = curl_exec($ch);

            if (empty($output)) {
                $this->db->query("UPDATE `employees` SET `status` = 'inactive' WHERE `id` = '" . (int)$e['id'] . "' ");
                //TODO: create additional cron which must try to download this pictures
            } else {

                file_put_contents(DIR_AVATAR . $image, $output);
                if (file_exists(DIR_AVATAR . $image)) {
                    $this->db->query("UPDATE `employees` SET `status` = 'finished', `avatar` = '" . $image . "' WHERE `id` = '" . (int)$e['id'] . "' ");
                }

            }
            curl_close($ch);

        }

    }

    /**
     * Checking if image can be download
     * @see _canDownloadAvatars()
     */
    private function _canBeDownload($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);
        if ($result !== FALSE) {
            return true;
        }

        return false;
    }

    private function _logError($title, $output)
    {

        $dirpath = DIR_CLASSES . "/logs/";
        if (!file_exists($dirpath)) {
            $oldmask = umask(0);
            mkdir($dirpath, 0777);
            umask($oldmask);
        }
        file_put_contents($dirpath . 'log_' . date("j_n_Y") . '.log', $output . "\r\n", FILE_APPEND | LOCK_EX);
        mail($this->admin, $title, $output);

    }

}

new cron($argv);
?>
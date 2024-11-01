<?php


include_once('TrollHunter_LifeCycle.php');

class TrollHunter_Plugin extends TrollHunter_LifeCycle {

    /**
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        return array(
            'apiKey' => array(__('Enter Gavagai apiKey', 'troll-hunter')),
            'language' => array(__('Language', 'troll-hunter'), 'sv', 'en', 'fi')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'Troll Hunter';
    }

    protected function getMainPluginFileName() {
        return 'troll-hunter.php';
    }

    /**
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {

        // Add options administration page
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        global $apikey;
        $apikey = $this->getOption("apiKey");

        global $language;
        $language = $this->getOption("language", "en");

        add_filter('pre_comment_approved', 'gavagai_blocker_check');


        function gavagai_blocker_check($pre_approved)
        {
            global $commentdata;
            $gavagai_approved = 0;

            $tonality = get_tonality($commentdata['comment_content']);
            if ($tonality == null) {
                error_log('Troll Hunter analysis failed; returning approval status 0.');
                return 0;
            }

            $score = bully_score($tonality);
            if($score == 0){
                $gavagai_approved = 1;
            }


            error_log("Troll Hunter score: " . $score . ", approved: " . $gavagai_approved . ", pre-approval state: " . $pre_approved);
            if($pre_approved != 1) {
                return $pre_approved;
            }
            if ($gavagai_approved == 0)
            {
                return 0;
            }

            return $pre_approved;
        }

        function get_tonality($text)
        {
            global $apikey;
            global $language;

            error_log("apikey=" . $apikey);
            error_log("language=" . $language);

            $service_url = 'https://api.gavagai.se/v3/tonality?language=' . $language . '&apiKey=' . $apikey . '&tones=violence,profanity,hate&affiliate=troll-hunter';

            $document = array('body' => $text, 'id' => 'trollhunter');
            $payload = array('documents' => array($document));
            $json = json_encode($payload);

            // post to gavagai api
            $http = curl_init($service_url);
            curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($http, CURLOPT_POSTFIELDS, $json);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($http, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
            $result = curl_exec($http);
            $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
            curl_close($http);

            error_log('Gavagai API status: ' . $http_status . '; ' . $result . '; ' . $service_url);

            if ($http_status > 200) {
                return null;
            }

            $res = json_decode($result, true);
            $tonality_array = $res['documents'][0]['tonality'];
            $tonality = map_tonality($tonality_array);
            return $tonality;
        }

        function map_tonality($tonality_array)
        {
            $new_array = array();

            foreach ($tonality_array as $key => $value) {
                $new_array[$value['tone']] = $value['score'];
            }

            return $new_array;
        }

        function bully_score($tonality){
            return $tonality['hate'] + $tonality['violence'] + $tonality['profanity'];
        }

    }


}

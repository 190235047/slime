# SlimeFramework Component MultiProcess

* Example

    class MyMP extends \SlimeFramework\Component\MultiProcess\MPPoolModel
    {
        /**
         * @return string|null
         */
        protected function getMessage()
        {
            $i = rand(1, 100);
            if ($i<=50) {
                return null;
            }
            return json_encode(array(rand(1, 100), rand(1, 100)));
        }
    }

    class MyJob extends \SlimeFramework\Component\MultiProcess\Job
    {
        public function run()
        {
            $aArr = json_decode($this->sMessage);
            $this->Logger->debug('job run:' . ($aArr[0] + $aArr[1]));
            return true;
        }
    }

    $MP = new MyMP(10, 'fifo', 'MyJob', 1000, new \SlimeFramework\Component\Log\Logger(array(new \SlimeFramework\Component\Log\Writer_STDFD())));
    $MP->run();
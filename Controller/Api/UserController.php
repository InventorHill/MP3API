<?php
    class UserController extends BaseController 
    {
        public function registerAction()
        {
            $register = new Register();
            $dataToSend = $register->register();
            $this->sendOutput($dataToSend[0], $dataToSend[1], $dataToSend[2]);
        }

        public function checkUpdateAction()
        {
            $check_update = new CheckUpdate();
            $dataToSend = $check_update->checkUpdate();
            $this->sendOutput($dataToSend[0], $dataToSend[1], $dataToSend[2]);
        }

        public function addAction()
        {
            $add_file = new AddFile();
            $dataToSend = $add_file->addFile();
            $this->sendOutput($dataToSend[0], $dataToSend[1], $dataToSend[2]);
        }

        public function updateAction()
        {
            $update_file = new UpdateVersion();
            $dataToSend = $update_file->updateVersion();
            $this->sendOutput($dataToSend[0], $dataToSend[1], $dataToSend[2]);
        }

        public function downloadAction()
        {
            $download_file = new DownloadFile();
            $dataToSend = $download_file->downloadFile();
            $this->sendOutput($dataToSend[0], $dataToSend[1], $dataToSend[2]);
        }

        public function uploadAction()
        {
            $upload_file = new UploadFile();
            $dataToSend = $upload_file->uploadFile();
            $this->sendOutput($dataToSend[0], $dataToSend[1], $dataToSend[2]);
        }

        public function onlineAction()
        {
            $this->sendOutput(json_encode(array('OK' => 'Server Online')), array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
        }
    }
?>
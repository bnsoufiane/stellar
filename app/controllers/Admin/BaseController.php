<?php
namespace Admin;

use \BaseController as BaseAppController;

class BaseController extends BaseAppController {
	protected $layout = 'admin.layouts.base';

	public static function handleUpload($paramName) {
        $file = \Request::instance()->getContent();

        $path = pathinfo($_GET[$paramName]);
        $filename = substr(sha1(time()), 0, 10) . '.' . addslashes($path['extension']);

		$filepath = public_path() . '/uploads/' . $filename;
        file_put_contents($filepath, $file);

		return array(
			'filepath' => $filepath,
			'filename' => $filename
		);
	}

    public function parse_file()
    {
        if (strpos(getcwd (),'public') !== false) {
            $baselink = 'uploads/';
        }else{
            $baselink = 'public/uploads/';
        }

        $filename = $baselink . (\Input::get('file'));

        $str = file_get_contents($filename);
        $str = str_replace(chr(13), chr(13) . chr(10), $str);
        $str = str_replace(chr(10) . chr(10), chr(10), $str);
        file_put_contents($filename, $str);


        $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        unset($data[0]);
        $data = array_values($data);

        return json_encode($data);
    }

}

<?php

/**
 * delete_old_files() takes a list of files and deletes files older
 * than the specified number of seconds.
 *
 * @param $files_list mixed (either string or array)
 * @param $older_than integer minimum number of seconds old a file
 *        should be before it is deleted.
 */
function delete_old_files( $files_list , $older_than )
{
	if( is_string($files_list) && is_file($files_list) && is_writable($files_list) && ((time() - $older_than) > filemtime($files_list) ))
	{
		unlink($files_list);//debug('deleted: '.$files_list);
	}
	elseif(is_array($files_list))
	{
		if(isset($files_list['path']) && isset($files_list['file']))
		{
			delete_old_files($files_list['path'].$files_list['file'] , $older_than);
		}
		else
		{
			foreach($files_list as $file)
			{
				delete_old_files($file , $older_than );
			};
		};
	};
}

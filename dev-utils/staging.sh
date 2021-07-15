thisdir=$PWD
cd ../

function syncup
{

	rsync -azrP --no-perms --owner='ernest' --group='ernest' src/  ernest@116.202.51.225:/home/ernest/common-crawl-src

}

syncup

inotifywait -mr --timefmt '%d/%m/%y %H:%M' --format '%T %w %f' \
-e close_write -e create -e delete -e move src/ | while read date time dir file; do
 FILECHANGE=${dir}${file}
       # convert absolute path to relative#
       FILECHANGEREL=`echo "$FILECHANGE" | sed 's_'$CURPATH'/__'`
	syncup
done


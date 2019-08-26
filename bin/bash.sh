#!/bin/bash

echo "SELECT '' AS name "  > bin/images.data~
ls web/upload/public/image/ | xargs -i echo "UNION SELECT '{}' "  >> bin/images.data~
FileImageList=`cat bin/images.data~` 

cp src/UcaBundle/Resources/sql/GestionFichier/images.sql bin/temp.sql~
sed -i -e "s/{{ FileImageList }}/`echo $FileImageList`/g" bin/temp.sql~ 

mysql --defaults-extra-file=bin/credentials -N <  bin/temp.sql~ > bin/uselessFiles.data~

while read -r filename; do
  rm "web/upload/public/image/$filename"
done < bin/uselessFiles.data~

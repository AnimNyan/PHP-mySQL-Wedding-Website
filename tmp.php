<?php
// show 3 images on a row 
// https://stackoverflow.com/questions/57085327/display-multiple-images-in-html-table-column
$AllCommentImages = explode(",", $jsonArray[$jsonIndex]['Comment_Image']);
$html.='<tr><td><b>Photos:</b></td></tr>';
$columnNumber=0;

foreach($AllCommentImages  as $cimg) 
{
    if($columnNumber%3==0) $html.='<tr>';
    $commentmysock = getimagesize($cimg);
    $html.='<td><img style="border:15px solid white;border-radius:15px;" src="'.$cimg.'"'.$this->imageResize($commentmysock[0],$commentmysock[1], 200).'/></td>';
    $columnNumber++;
    if($columnNumber%3==0) $html.='</tr>';
}   

while($columnNumber%3!=0){
    $html.='<td></td>';
    $columnNumber++;
}

$html.= '</tr>';

?>
<img width="25%">
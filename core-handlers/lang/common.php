<?php

namespace aw2\common;

function updateInfo(array &$info,$index): array {

    $info['index']=$index;
    $info['counter']=$index-1;
    
    $info['first']=false;
    $info['last']=false;
    $info['between']=false;
    $info['odd']=false;
    $info['even']=false;
    
    if ($index % 2 != 0)
        $info['odd']= true;
    else
        $info['even']= true;
    if($index==1)$info['first']=true;
    if($index==$info['count'])$info['last']=true;
    if($index!=$info['count'])$info['between']=true;



// Return the updated array
return $info;
}

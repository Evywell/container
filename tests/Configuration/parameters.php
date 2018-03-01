<?php
return [
    'firstKey' => 3,
    'keyArray' => ['first_element', 'key' => 'value', 13, 54, ['other_array']],
    'myValue'  => '%firstKey%',
    'myComplexeValue' => ['element' => ['other_array' => 'super-%firstKey%']],
    'mySecondComplexeValue' => ['%myComplexeValue%', '%keyArray%']
];
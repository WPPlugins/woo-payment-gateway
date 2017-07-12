<?php
/**
 * @version 2.6.9
 */
?>
<div class="board">
	<div class="left"></div>
	<div class="right"></div>
	<div class="ball"></div>
</div>
<style>
body.bfwc-body form.braintree-donation .bfwc-payment-loader {
  background:rgba(0,0,0,.75);
}

body.bfwc-body form.braintree-donation .bfwc-payment-loader .board {
  position: absolute;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%);
  width:250px;
}

body.bfwc-body form.braintree-donation .bfwc-payment-loader .left,
body.bfwc-body form.braintree-donation .bfwc-payment-loader .right {
  height:50px;
  width:8px;
  background:white;
  display: inline-block;
  position:absolute;
}

body.bfwc-body form.braintree-donation .bfwc-payment-loader .left {
  left:0;
  animation: position1 2s linear infinite;
}
body.bfwc-body form.braintree-donation .bfwc-payment-loader .right {
  right:0;
  animation: position2 2s linear infinite;
}
body.bfwc-body form.braintree-donation .bfwc-payment-loader .ball,
body.bfwc-body form.braintree-donation .bfwc-payment-loader .ballhit {
  width:14px;
  height:14px;
  border-radius:50%;
  background:white;
  position:absolute;
  animation: bounce 2s linear infinite;
}
body.bfwc-body form.braintree-donation .bfwc-payment-loader .ballhit {
  padding:4px;
  margin:-6px 0 0 -6px;
  border-radius:50%;
  background:cornflowerblue;
  border:2px #fff solid;
  z-index:-1;
  animation: bounce2 2s linear infinite;
}
@keyframes position1 {
    0% {top:-60px;}
  25% {top:0;}
    50% {top:60px;}
  75% {top:-60px;}
    100% {top:-60px;}
}
@keyframes position2 {
    0% {top:60px;}
    25% {top:0;}
    50% {top:-60px;}
   75% {top:-60px;}
    100% {top:60px;}
}
@keyframes bounce {
    0% {top:-35px;left:10px;}
  25% {top:25px;left:225px;}
    50% {top:75px;left:10px;}
  75% {top:-35px;left:225px;}
  100% {top:-35px;left:10px;}
}
@keyframes bounce2 {
    0% {top:-35px;left:10px; border:2px cornflowerblue solid;}
  24% {border:2px cornflowerblue solid;}
  25% {top:25px;left:225px; border:2px #fff solid;}
  49% {border:2px cornflowerblue solid;}
    50% {top:75px;left:10px; border:2px #fff solid;}
  74% {border:2px cornflowerblue solid;}
  75% {top:-35px;left:225px; border:2px #fff solid;}
  99% {border:2px cornflowerblue solid;}
  100% {top:-35px;left:10px; border:2px #fff solid;}
}
</style>
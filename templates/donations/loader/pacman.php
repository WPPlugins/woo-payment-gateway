<?php
/**
 * @version 2.6.9
 */
?>
<div class="pacman-overlay">
	<div class="pacman"></div>
	<div class="pacman-dot"></div>
</div>
<style>
body.bfwc-body form.braintree-donation .pacman-overlay{
	width: 100%;
	height: 100%;
	background: rgba(0,0,0,.65);
}
@-webkit-keyframes pacman-animation {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(-30deg);
  }
}
@-moz-keyframes pacman-animation {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(-30deg);
  }
}
@-o-keyframes pacman-animation {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(-30deg);
  }
}
@keyframes pacman-animation {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(-30deg);
  }
}
@-webkit-keyframes down {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(30deg);
  }
}
@-moz-keyframes down {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(30deg);
  }
}
@-o-keyframes down {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(30deg);
  }
}
@keyframes down {
  0%, 100% {
    transform: rotate(0);
  }
  50% {
    transform: rotate(30deg);
  }
}
@-webkit-keyframes r-to-l {
  100% {
    margin-left: -1px;
  }
}
@-moz-keyframes r-to-l {
  100% {
    margin-left: -1px;
  }
}
@-o-keyframes r-to-l {
  100% {
    margin-left: -1px;
  }
}
@keyframes r-to-l {
  100% {
    margin-left: -1px;
  }
}

body.bfwc-body form.braintree-donation .pacman:before, 
body.bfwc-body form.braintree-donation .pacman:after {
  z-index: 9991;
  content: '';
  position: absolute;
  background: #FFC107;
  width: 100px;
  height: 50px;
  left: 50%;
  top: 50%;
  margin-left: -50px;
  margin-top: -50px;
  border-radius: 50px 50px 0 0;
  -webkit-animation: pacman-animation 0.4s infinite;
  -moz-animation: pacman-animation 0.4s infinite;
  -o-animation: pacman-animation 0.4s infinite;
  animation: pacman-animation 0.4s infinite;
}
body.bfwc-body form.braintree-donation .pacman:after {
  z-index: 9991;
  margin-top: -1px;
  border-radius: 0 0 50px 50px;
  -webkit-animation: down 0.4s infinite;
  -moz-animation: down 0.4s infinite;
  -o-animation: down 0.4s infinite;
  animation: down 0.4s infinite;
}
body.bfwc-body form.braintree-donation .pacman-dot {
  z-index: 999;
  position: absolute;
  left: 50%;
  top: 50%;
  width: 10px;
  height: 10px;
  margin-top: -5px;
  margin-left: 30px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 30px 0 0 #fff, 60px 0 0 #fff, 90px 0 0 #fff, 120px 0 0 #fff, 150px 0 0 #fff;
  -webkit-animation: r-to-l 0.4s infinite;
  -moz-animation: r-to-l 0.4s infinite;
  -o-animation: r-to-l 0.4s infinite;
  animation: r-to-l 0.4s infinite;
}

</style>
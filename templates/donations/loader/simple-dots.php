<?php
/**
 * @version 2.6.9
 */
?>
<div class="cs-loader">
  <div class="cs-loader-inner">
    <label>	&bull;</label>
    <label>	&bull;</label>
    <label>	&bull;</label>
    <label>	&bull;</label>
    <label>	&bull;</label>
    <label>	&bull;</label>
  </div>
</div>
<style>
body.bfwc-body form.braintree-donation .bfwc-payment-loader {
  background: rgba(43, 71, 90, 0.7);
}

body.bfwc-body form.braintree-donation .cs-loader {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
}

body.bfwc-body form.braintree-donation .cs-loader-inner {
  transform: translate(-50%, -50%);
  top: 50%;
  left: 50%;
  position: absolute;
  width: 100%;
  color: #FFF;
  padding: 0;
  text-align: center !important;
}

body.bfwc-body form.braintree-donation .cs-loader-inner label {
  font-size: 32px !important;
  opacity: 0;
  display:inline-block !important;
}

@keyframes dot-animation {
  0% {
    opacity: 0;
    transform: translateX(-300px);
  }
  33% {
    opacity: 1;
    transform: translateX(0px);
  }
  66% {
    opacity: 1;
    transform: translateX(0px);
  }
  100% {
    opacity: 0;
    transform: translateX(300px);
  }
}

@-webkit-keyframes dot-animation {
   0% {
    opacity: 0;
    transform: translateX(-300px);
  }
  33% {
    opacity: 1;
    transform: translateX(0px);
  }
  66% {
    opacity: 1;
    transform: translateX(0px);
  }
  100% {
    opacity: 0;
    transform: translateX(300px);
  }
}

body.bfwc-body form.braintree-donation .cs-loader-inner label:nth-child(6) {
  -webkit-animation: dot-animation 3s infinite ease-in-out;
  animation: dot-animation 3s infinite ease-in-out;
}

body.bfwc-body form.braintree-donation .cs-loader-inner label:nth-child(5) {
  -webkit-animation: dot-animation 3s 100ms infinite ease-in-out;
  animation: dot-animation 3s 100ms infinite ease-in-out;
}

body.bfwc-body form.braintree-donation .cs-loader-inner label:nth-child(4) {
  -webkit-animation: dot-animation 3s 200ms infinite ease-in-out;
  animation: dot-animation 3s 200ms infinite ease-in-out;
}

body.bfwc-body form.braintree-donation .cs-loader-inner label:nth-child(3) {
  -webkit-animation: dot-animation 3s 300ms infinite ease-in-out;
  animation: dot-animation 3s 300ms infinite ease-in-out;
}

body.bfwc-body form.braintree-donation .cs-loader-inner label:nth-child(2) {
  -webkit-animation: dot-animation 3s 400ms infinite ease-in-out;
  animation: dot-animation 3s 400ms infinite ease-in-out;
}

body.bfwc-body form.braintree-donation .cs-loader-inner label:nth-child(1) {
  -webkit-animation: dot-animation 3s 500ms infinite ease-in-out;
  animation: dot-animation 3s 500ms infinite ease-in-out;
}
</style>
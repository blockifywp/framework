(()=>{const e=()=>{var e;const t=window.matchMedia("(prefers-reduced-motion: reduce)");if(!t||t.matches)return;const n=new IntersectionObserver((e=>{e.forEach((e=>{const t=e.target,o="infinite"===t.style.animationIterationCount;if(e.isIntersecting&&!o){var a,i;t.classList.add("animate"),t.style.opacity="0",t.style.transform="none";const e=null!==(a=1e3*parseFloat(t?.style?.animationDuration?.replace("s","")))&&void 0!==a?a:1e3,o=null!==(i=1e3*parseFloat(t?.style?.animationDelay?.replace("s","")))&&void 0!==i?i:0;setTimeout((()=>{t.style.opacity="",t.style.transform=""}),e+o),n.unobserve(t)}}))}),{rootMargin:null!==(e=window?.blockify?.animationOffset)&&void 0!==e?e:"0px 0px 50px 0px"}),o=document.querySelectorAll(".has-animation");for(const e of o)n.observe(e)};document.addEventListener("DOMContentLoaded",e),window.addEventListener("resize",e)})();
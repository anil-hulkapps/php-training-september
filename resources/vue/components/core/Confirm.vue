<template>
    <div  v-show="dialog"  role="dialog" aria-labelledby="modal-header6" @keydown.esc="cancel" class="Polaris-Modal-Dialog">
        <div class="Polaris-Modal-Dialog__Modal " style="top: 50%;left: 50%;margin:0;transform: translate(-50%, -50%);z-index: 999;height: fit-content;position: fixed;">
            <div class="Polaris-Modal-Header">
                <div id="modal-header6" class="Polaris-Modal-Header__Title">
                    <h2 class="Polaris-DisplayText Polaris-DisplayText--sizeSmall">{{ title }}</h2>
                </div>
                <button class="Polaris-Modal-CloseButton" v-on:click="cancel">
                    <span class="Polaris-Icon Polaris-Icon--colorInkLighter Polaris-Icon--isColored">
                        <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                            <path d="M11.414 10l6.293-6.293a.999.999 0 1 0-1.414-1.414L10 8.586 3.707 2.293a.999.999 0 1 0-1.414 1.414L8.586 10l-6.293 6.293a.999.999 0 1 0 1.414 1.414L10 11.414l6.293 6.293a.997.997 0 0 0 1.414 0 .999.999 0 0 0 0-1.414L11.414 10z" fill-rule="evenodd"></path>
                        </svg>
                    </span>
                </button>
            </div>
            <div class="Polaris-Modal__BodyWrapper">
                <div class="Polaris-Modal__Body Polaris-Scrollable Polaris-Scrollable--vertical" data-polaris-scrollable="true">
                    <div class="p-4">
                        {{ message }}
                    </div>
                </div>
            </div>
            <div class="Polaris-Modal-Footer">
                <div class="Polaris-Modal-Footer__FooterContent">
                    <div class="Polaris-Stack Polaris-Stack--alignmentCenter">
                        <div class="Polaris-Stack__Item Polaris-Stack__Item--fill"></div>
                        <div class="Polaris-Stack__Item">
                            <div class="Polaris-ButtonGroup">
                                <div class="Polaris-ButtonGroup__Item">
                                    <button type="button" @click="cancel" class="Polaris-Button">
                                        <span class="Polaris-Button__Content"><span class="Polaris-Button__Text">{{"No"}}</span></span>
                                    </button>
                                    <button type="button" @click="agree" class="Polaris-Button Polaris-Button--primary">
                                        <span class="Polaris-Button__Content"><span class="Polaris-Button__Text">{{"Yes"}}</span></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="plan_overlay" style="position: fixed;top: 0px;right: 0px;bottom: 0px;left: 0px;background: black;z-index: 99;opacity: 0.2;"></div>
    </div>
</template>

<script>
    export default {
        data: () => ({
            dialog: false,
            resolve: null,
            reject: null,
            message: null,
            title: null,
            options: {
                color: 'primary',
                width: 500,
                zIndex: 200
            }
        }),
        methods: {
            open(title, message, options) {
                this.dialog = true
                this.title = title
                this.message = message
                this.options = Object.assign(this.options, options)
                return new Promise((resolve, reject) => {
                    this.resolve = resolve
                    this.reject = reject
                })
            },
            agree() {
                this.resolve(true)
                this.dialog = false
            },
            cancel() {
                this.resolve(false)
                this.dialog = false
            }
        }
    }
</script>

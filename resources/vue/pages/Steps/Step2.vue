<template>
    <PPage narrowWidth title="Step 2/5" :breadcrumbs='[{"content":"Step 2/5","to":"/step-1"}]'>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Script editor page</PHeading>
                    Please insert the following code into your <b>Script Editor App</b> within the <b>Line Items</b> section. If you don't know how to code, please   <PLink @click="calendlyPopup">book a call</PLink> with our support agent.<br /><br />
                    <PFormLayout>
                        <!--<PTextField :minHeight="100" multiline id="input_field" value="adadad" />-->
                        <PCard subdued ref="mylink"><br />
                            &nbsp;&nbsp;&nbsp;Input.cart.line_items.each do |line_item|<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if line_item.properties["excise_tax"]<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dis_price = line_item.properties["excise_tax"]<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if dis_price != "" && dis_price != 0<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;line_item.change_line_price(Money.new(cents: dis_price), message: "")<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;end<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;end<br />
                            &nbsp;&nbsp;&nbsp;end<br /><br />

                            &nbsp;&nbsp;&nbsp;Output.cart = Input.cart<br /><br />

                        </PCard>
                        <PButton @click="copyScript()">Copy Code</PButton>
                    </PFormLayout>
                </PCardSection>
            </PCard>
            <PLayoutSection>
                <PPageActions :primaryAction="{content: 'Continue', onAction: handleButtonEvent}"></PPageActions>
            </PLayoutSection>
        </PLayout>
    </PPage>
</template>

<script>

    export default {
        name: "Step2",
        data() {
            return {

            }
        },
        computed: {},
        methods: {
            copyScript() {
                const el = document.createElement('textarea');
                el.value = "Input.cart.line_items.each do |line_item|\n" +
                    "   if line_item.properties[\"excise_tax\"]\n" +
                    "      dis_price = line_item.properties[\"excise_tax\"]\n" +
                    "      if dis_price != \"\" && dis_price != 0<br />\n" +
                    "         line_item.change_line_price(Money.new(cents: dis_price), message: \"\")\n" +
                    "      end\n" +
                    "   end\n" +
                    "end\n" +
                    "\n" +
                    "Output.cart = Input.cart";
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                this.$root.$toast('Copied');
            },
            handleAction() {
                this.$router.push('/step-1');
            },
            handleButtonEvent() {
                this.$router.push('/step-3');
            },
            calendlyPopup() {
                Calendly.initPopupWidget({
                    url: 'https://calendly.com/avalara-support/schedule-a-call'
                });
                return false;
            },
        },
        async created() {}
    }
</script>

<style scoped>

</style>


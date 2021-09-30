<template>
    <PPage narrowWidth title="Step 2/5" :breadcrumbs='[{"content":"Step 2/5","to":"/step-1"}]'>
        <PIcon slot="titleMetadata" source="CircleInformationMajor" color="interactive" @click.native="is_modal=true" style="float: left"/>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Script editor page</PHeading>
                    Please insert the following code into your <b>Script Editor App</b> within the <b>Line Items</b> section. If you don't know how to code, please   <PLink @click="calendlyPopup">book a call</PLink> with our support agent.<br /><br />
                    <PFormLayout>
                        <PCard sectioned ref="mylink" style="white-space: pre-wrap;">{{scriptEditorCode}}</PCard>
                        <PButton @click="copyScript()">Copy Code</PButton>
                    </PFormLayout>
                </PCardSection>
            </PCard>
            <PModal
                :open="is_modal"
                sectioned
                title="To add script in script editor for proper excise tax"
                @close="is_modal=false"
                large
            >
                <PList type="number">
                    <PListItem>You need to install script editor app to paste this code for getting proper excise price at checkout.</PListItem>
                    <img src="images/step/step2/script.png" alt="App" width="100%"><br>
                    <PListItem>You will see this type of home page. Press Create script button.</PListItem><br>
                    <img src="images/step/step2/script2.png" alt="App" width="100%"><br>

                    <PListItem>Then select Amount off a product.</PListItem>
                    <img src="images/step/step2/script3.png" alt="Express" width="100%"><br>
                    <PListItem>Now paste your copied script in next step as shown in picture.</PListItem><br>
                    <img src="images/step/step2/script4.png" alt="Express" width="100%"><br>
                </PList>
            </PModal>
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
            is_modal: false,
            scriptEditorCode: `Input.cart.line_items.each do |line_item|
  if line_item.properties["avalara_excise_tax"]
    avalaraExciseTax = line_item.properties["avalara_excise_tax"]
    if avalaraExciseTax && avalaraExciseTax != ""
      line_item.change_line_price(Money.new(cents: avalaraExciseTax), message: "")
    end
  end
end

Output.cart = Input.cart
`
        }
    },
    computed: {},
    methods: {
        copyScript() {
            const el = document.createElement('textarea');
            el.value = this.scriptEditorCode;
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


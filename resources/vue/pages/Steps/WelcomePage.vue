<template>
    <PPage narrowWidth>
        <PLayout sectioned>
            <PHeading>{{shop_name}}, Thank you for installing our app</PHeading>
            <p>Get ready to experience the freedom to stay focused on your business, not on tax management.</p><br />
            <PCard title="Introduction">
                <PCardSection>
                    Welcome to Praella! We give Shopify stores the mechanisms to thrive. This app is the only connector that bridges Avalara with Shopify and supports brands set up automated Excise Tax compliance.
                    <br /><br />
                    <PHeading>How it works</PHeading><br />
                    The app closely works with Avalara to calculate Excise Tax for all the Shopify Plus transactions triggered by what your customers buy at the checkout.
                </PCardSection>
            </PCard>
            <PCard title="Setup">
                <PCardSection>
                    <PCardSubsection>
                        The app requires a few adjustments to the theme code. Book a call & get direct help from our customer support with the tweaks.<br /><br />
                        Ensure to stay equipped with the below list of details during the onboarding process:<br /><br />
                        1. Avalara Account<br />
                        2. Product configured in Avalara with excise taxation<br /><br />
                        <PButton @click="calendlyPopup" primary>Book a call</PButton><br /><br />
                        <router-link to="/step-1">I am technical sound and I want to configure on my own</router-link>
                    </PCardSubsection>
                </PCardSection>
            </PCard>
        </PLayout>
    </PPage>
</template>

<script>

    export default {
        name: "WelcomePage",
        data() {
            return {
                shop_name: ''
            }
        },
        computed: {},
        methods: {
            async getData() {
                try {
                    let response = await axios.get('/api/settings/store-detail');
                    this.shop_name = response.data.shop_name;
                } catch ({response}) {

                }
            },
            calendlyPopup() {
                Calendly.initPopupWidget({
                    url: 'https://calendly.com/avalara-support/schedule-a-call'
                });
                return false;
            },
        },
        async created() {
            await this.getData();
        }
    }
</script>

<style scoped>

</style>


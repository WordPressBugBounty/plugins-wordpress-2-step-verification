export default {
    data(){
        return {
            i18n: {

                __: wp.i18n.__,
                _x: wp.i18n._x,
                _n: wp.i18n._n,
                _nx: wp.i18n._nx
            }
        }
    },
    methods:{
        sprintf: wp.i18n.sprintf,
    }
};

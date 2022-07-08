// Import
import { ApiPromise, WsProvider } from '@polkadot/api';


// Construct
const wsProvider = new WsProvider('wss://rpc.polkadot.io');
const api = await ApiPromise.create({ provider: wsProvider });

// Do something
console.log(api.genesisHash.toHex());

import { stringToHex } from "@polkadot/util";

// `account` is of type InjectedAccountWithMeta 
// We arbitrarily select the first account returned from the above snippet
const account = allAccounts[0];

// to be able to retrieve the signer interface from this account
// we can use web3FromSource which will return an InjectedExtension type
const injector = await web3FromSource(account.meta.source);


// this injector object has a signer and a signRaw method
// to be able to sign raw bytes
const signRaw = injector?.signer?.signRaw;

if (!!signRaw) {
    // after making sure that signRaw is defined
    // we can use it to sign our message
    const { signature } = await signRaw({
        address: account.address,
        data: stringToHex('message to sign'),
        type: 'bytes'
    });
}
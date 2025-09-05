import {startStimulusApp} from '@symfony/stimulus-bridge';
import Overlay from "./controllers/components/overlay/overlay";
import OverlayTrigger from "./controllers/components/overlay/overlay-trigger";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

// register any custom, 3rd party controllers here
app.register('overlay', Overlay);
app.register('overlay-trigger', OverlayTrigger);

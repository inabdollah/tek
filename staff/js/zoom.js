// Get the image and the magnifier buttons
const seatsContainer = document.getElementById("seatsContainer");
const minusButton = document.getElementById("minus");
const plusButton = document.getElementById("plus");
const resetButton = document.getElementById("reset");

// Set the initial scale and position of the image
let scale = 1;
let posX = 0;
let posY = 0;

if(minusButton!=null){
    // Add event listeners to the buttons
minusButton.addEventListener("click", () => {
  scale -= 0.1;
  updateImage();
});

}
if(plusButton!=null){
    
plusButton.addEventListener("click", () => {
  scale += 0.1;
  updateImage();
});

}
if(resetButton!=null){
resetButton.addEventListener("click", () => {
  scale = 1;
  posX = 0;
  posY = 0;
  updateImage();
});

}

// Add event listeners to the image
let isDragging = false;
let startX, startY, startDragX, startDragY;
let pinchStartDistance, pinchStartScale;

seatsContainer.addEventListener("mousedown", startDrag);
seatsContainer.addEventListener("touchstart", startDrag);

seatsContainer.addEventListener("mouseup", endDrag);
seatsContainer.addEventListener("touchend", endDrag);

seatsContainer.addEventListener("mousemove", drag);
seatsContainer.addEventListener("touchmove", drag);

seatsContainer.addEventListener("touchstart", startPinch);
seatsContainer.addEventListener("touchmove", pinch);

function startDrag(e) {
  if (e.touches) {
    if (e.touches.length !== 1) return;
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
  } else {
    startX = e.clientX;
    startY = e.clientY;
  }
  startDragX = posX;
  startDragY = posY;
  isDragging = true;
}

function endDrag(e) {
  isDragging = false;
}

function drag(e) {
  e.preventDefault();
  if (!isDragging) return;
  if (e.touches) {
    if (e.touches.length !== 1) return;
    const touchX = e.touches[0].clientX;
    const touchY = e.touches[0].clientY;
    posX = startDragX + touchX - startX;
    posY = startDragY + touchY - startY;
  } else {
    posX = startDragX + e.clientX - startX;
    posY = startDragY + e.clientY - startY;
  }
  updateImage();
}

function startPinch(e) {
  if (e.touches.length !== 2) return;
  pinchStartDistance = getDistance(e.touches[0], e.touches[1]);
  pinchStartScale = scale;
}

function pinch(e) {
  if (e.touches.length !== 2) return;
  const distance = getDistance(e.touches[0], e.touches[1]);
  const scaleDelta = (distance / pinchStartDistance) * pinchStartScale;
  scale = Math.max(0.1, scaleDelta);
  updateImage();
}

// Function to update the image scale and position
function updateImage() {
  seatsContainer.style.transform = `translate(${posX}px, ${posY}px) scale(${scale})`;
}

function getDistance(touch1, touch2) {
  const dx = touch1.clientX - touch2.clientX;
  const dy = touch1.clientY - touch2.clientY;
  return Math.sqrt(dx * dx + dy * dy);
}

// let m = pjQ.$;
// window.initialScale=null;

// m(document).ready(function() {

//     if (m(window).width() < m("#seatsContainer").width()) {

//         let m_scale = m(window).width() / m("#seatsContainer").width();

//         let left = m("#seatsContainer").offset().left;

//         let x = m("#seatsContainer").offset().left + (m(window).width()-25);
        
//         window.initialScale=' scale(' + m_scale + ') translateX(-' + x + 'px)';

//         m("#seatsContainer").css('transform', window.initialScale);

//     }

// });




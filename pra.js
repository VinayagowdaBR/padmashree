// let start = 0;
// let end = 10;

// for (let i = start; i <= end; i++) {
//   if (isPrime(i)) {
//     console.log(i);
//   }
// }

// function isPrime(num) {
//   if (num < 2) {
//     return false;
//   }

//   //   console.log(`  this is  the return num < 2 `, num < 2);
//   for (let j = 2; j < num; j++) {
//     console.log(` this is the num ${num} and this is the j ${j}`);

//     if (num % j === 0) {
//       return false;
//     }
//   }
//   return true;
// }

// let start = 0;
// let end = 10;

// for (let i = start; i <= end; i++) {
//   if (isPrime(i)) {
//     console.log(i);
//   }
// }

// function isPrime(num) {
//   if (num < 2) return false;

//   for (let j = 2; j <= num / 2; j++) {
//     console.log(` this is the num ${num} and this is the j ${j}`);

//     if (num % j === 0) {
//       return false;
//     }
//   }
//   return true;
// }
let start = 0;
let end = 10;

for (let i = start; i <= end; i++) {
  if (isPrime(i)) {
    console.log(i);
  }
}

function isPrime(num) {
  if (num < 2) return false;
  for (let j = 2; j <= Math.sqrt(num); j++) {
    console.log(` this is the num ${num} and this is the j ${j}`);

    if (num % j === 0) {
      return false;
    }
  }
  return true;
}
